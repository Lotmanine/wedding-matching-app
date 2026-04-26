<?php

declare(strict_types=1);

namespace Tests\Unit\Whatsapp;

use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Support\Whatsapp\InboundButtonContext;
use App\Support\Whatsapp\InboundButtonException;
use App\Support\Whatsapp\SignedButtonPayload;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SignedButtonPayloadDecodeAndValidateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(string $whatsapp = '+213555111222'): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => $whatsapp,
            'whatsapp' => $whatsapp,
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['ar', 'fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);

        return $u->fresh();
    }

    private function makeServiceRequest(string $status = 'round1', ?int $unlockedBy = null): ServiceRequest
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Fatima',
            'couple_wilaya_code' => '16',
            'services_needed'    => ['photo'],
        ]);

        return ServiceRequest::create([
            'wed_request_id'      => $wr->id,
            'service_type'        => 'photo',
            'status'              => $status,
            'current_round'       => 1,
            'round_started_at'    => now(),
            'unlocked_by_user_id' => $unlockedBy,
        ]);
    }

    public function test_happy_path_returns_context(): void
    {
        $sr = $this->makeServiceRequest();
        $p  = $this->makeProvider('+213555111222');

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $ctx = SignedButtonPayload::decodeAndValidate($token, '+213555111222');

        $this->assertInstanceOf(InboundButtonContext::class, $ctx);
        $this->assertSame($sr->id, $ctx->serviceRequest->id);
        $this->assertSame($p->id, $ctx->provider->id);
        $this->assertTrue($ctx->isAccept());
    }

    public function test_tampered_token_throws_tampered(): void
    {
        $sr = $this->makeServiceRequest();
        $p  = $this->makeProvider();

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');
        $bad = substr($token, 0, 30) . 'XYZXYZ' . substr($token, 36);

        $this->expectException(InboundButtonException::class);
        try {
            SignedButtonPayload::decodeAndValidate($bad, $p->whatsapp);
        } catch (InboundButtonException $e) {
            $this->assertSame(InboundButtonException::REASON_TAMPERED, $e->reason);
            throw $e;
        }
    }

    public function test_mismatched_phone_throws(): void
    {
        $sr = $this->makeServiceRequest();
        $p  = $this->makeProvider('+213555111222');

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        try {
            SignedButtonPayload::decodeAndValidate($token, '+213999000000');
            $this->fail('Expected InboundButtonException');
        } catch (InboundButtonException $e) {
            $this->assertSame(InboundButtonException::REASON_MISMATCHED_PHONE, $e->reason);
        }
    }

    public function test_round_closed_throws(): void
    {
        $sr = $this->makeServiceRequest(status: 'expired');
        $p  = $this->makeProvider('+213555111222');

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        try {
            SignedButtonPayload::decodeAndValidate($token, '+213555111222');
            $this->fail('Expected InboundButtonException');
        } catch (InboundButtonException $e) {
            $this->assertSame(InboundButtonException::REASON_ROUND_CLOSED, $e->reason);
        }
    }

    public function test_already_won_throws(): void
    {
        $other = User::factory()->create(['role' => 'provider', 'status' => 'active']);
        $other->assignRole('provider');

        $p = $this->makeProvider('+213555111222');
        $sr = $this->makeServiceRequest(status: 'accepted', unlockedBy: $other->id);

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        try {
            SignedButtonPayload::decodeAndValidate($token, '+213555111222');
            $this->fail('Expected InboundButtonException');
        } catch (InboundButtonException $e) {
            $this->assertSame(InboundButtonException::REASON_ALREADY_WON, $e->reason);
        }
    }

    public function test_expired_token_throws(): void
    {
        $sr = $this->makeServiceRequest();
        $p  = $this->makeProvider('+213555111222');

        // Encode 8 days ago
        Carbon::setTestNow(now()->subDays(8));
        $oldToken = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');
        Carbon::setTestNow();

        try {
            SignedButtonPayload::decodeAndValidate($oldToken, '+213555111222');
            $this->fail('Expected InboundButtonException');
        } catch (InboundButtonException $e) {
            $this->assertSame(InboundButtonException::REASON_EXPIRED, $e->reason);
        }
    }

    public function test_decoded_payload_includes_issued_at(): void
    {
        Carbon::setTestNow('2026-04-27 12:00:00');
        $token = SignedButtonPayload::encode('SRV-X', 1, 'accept');
        $decoded = SignedButtonPayload::decode($token);

        $this->assertArrayHasKey('issued_at', $decoded);
        $this->assertEqualsWithDelta(now()->timestamp, $decoded['issued_at'], 5);
        Carbon::setTestNow();
    }
}
