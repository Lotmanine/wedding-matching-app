<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Whatsapp;

use App\Models\AuditLog;
use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Models\WhatsappMessage;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\Results\AcceptResult;
use App\Services\Matching\Results\RefuseResult;
use App\Support\Whatsapp\SignedButtonPayload;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class InboundButtonTapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function asN8n(): User
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['n8n-service']);

        return $u;
    }

    private function makeProvider(string $whatsapp = '+213555111222'): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'whatsapp' => $whatsapp,
            'phone'    => $whatsapp,
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['ar'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);

        return $u->fresh();
    }

    private function makeSr(string $status = 'round1', ?int $unlockedBy = null): ServiceRequest
    {
        $wr = WedRequest::factory()->create(['couple_wilaya_code' => '16', 'services_needed' => ['photo']]);

        return ServiceRequest::create([
            'wed_request_id'      => $wr->id,
            'service_type'        => 'photo',
            'status'              => $status,
            'current_round'       => 1,
            'round_started_at'    => now(),
            'unlocked_by_user_id' => $unlockedBy,
        ]);
    }

    private function payload(string $token, string $waMessageId = 'wamid.HBgM_TEST_001', string $fromPhone = '+213555111222'): array
    {
        return [
            'wa_message_id'  => $waMessageId,
            'from_phone'     => $fromPhone,
            'button_payload' => $token,
            'button_text'    => 'قبول',
            'in_reply_to'    => 'wamid.OUTBOUND_001',
            'timestamp'      => '1745596800',
        ];
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/v1/whatsapp/inbound/button-tap', [])->assertStatus(401);
    }

    public function test_wrong_ability_returns_403(): void
    {
        $u = User::factory()->create();
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['nope']);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', [])->assertStatus(403);
    }

    public function test_accept_intent_calls_acceptance_service(): void
    {
        $this->asN8n();
        $sr = $this->makeSr();
        $p  = $this->makeProvider();

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $mock->shouldReceive('accept')
            ->once()
            ->withArgs(function (User $provider, ServiceRequest $serviceRequest, string $via) use ($p, $sr) {
                return $provider->id === $p->id
                    && $serviceRequest->id === $sr->id
                    && $via === 'whatsapp';
            })
            ->andReturn(AcceptResult::success($sr, $p));
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted')
            ->assertJsonPath('data.service_request_uid', $sr->service_uid)
            ->assertJsonPath('data.provider_id', $p->id);

        $this->assertSame(1, WhatsappMessage::where('direction', 'inbound')->count());
    }

    public function test_refuse_intent_calls_acceptance_service(): void
    {
        $this->asN8n();
        $sr = $this->makeSr();
        $p  = $this->makeProvider();

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'refuse');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $mock->shouldReceive('refuse')
            ->once()
            ->withArgs(fn (User $u, ServiceRequest $s) => $u->id === $p->id && $s->id === $sr->id)
            ->andReturn(RefuseResult::success($sr));
        $mock->shouldNotReceive('accept');
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))
            ->assertOk()
            ->assertJsonPath('data.status', 'refused');
    }

    public function test_repeated_wa_message_id_is_idempotent(): void
    {
        $this->asN8n();
        $sr = $this->makeSr();
        $p  = $this->makeProvider();

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $mock->shouldReceive('accept')->once()->andReturn(AcceptResult::success($sr, $p));
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))->assertOk();

        // Second call with same wa_message_id — service should NOT be invoked again
        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))
            ->assertOk()
            ->assertJsonPath('data.status', 'already_processed');

        $this->assertSame(1, WhatsappMessage::where('direction', 'inbound')->count());
    }

    public function test_tampered_token_returns_rejected_with_audit_entry(): void
    {
        $this->asN8n();
        $p = $this->makeProvider();

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $mock->shouldNotReceive('accept');
        $mock->shouldNotReceive('refuse');
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload('eyJpdiI6Ik5PUEUiLCJ2YWx1ZSI6Ik5PUEUiLCJtYWMiOiJOT1BFIn0='))
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.reason', 'tampered');

        $this->assertTrue(AuditLog::where('action', 'whatsapp.button_tap.rejected')->exists());
    }

    public function test_mismatched_phone_returns_rejected(): void
    {
        $this->asN8n();
        $sr = $this->makeSr();
        $p  = $this->makeProvider('+213555111222');

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $mock->shouldNotReceive('accept');
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token, fromPhone: '+213999000000'))
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.reason', 'mismatched_phone');
    }

    public function test_round_closed_returns_rejected(): void
    {
        $this->asN8n();
        $sr = $this->makeSr(status: 'expired');
        $p  = $this->makeProvider();

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))
            ->assertOk()
            ->assertJsonPath('data.reason', 'round_closed');
    }

    public function test_already_won_returns_rejected(): void
    {
        $this->asN8n();
        $other = User::factory()->create(['role' => 'provider', 'status' => 'active']);
        $other->assignRole('provider');

        $p  = $this->makeProvider();
        $sr = $this->makeSr(status: 'accepted', unlockedBy: $other->id);

        $token = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($token))
            ->assertOk()
            ->assertJsonPath('data.reason', 'already_won');
    }

    public function test_expired_token_returns_rejected(): void
    {
        $this->asN8n();
        $sr = $this->makeSr();
        $p  = $this->makeProvider();

        \Illuminate\Support\Carbon::setTestNow(now()->subDays(10));
        $oldToken = SignedButtonPayload::encode($sr->service_uid, $p->id, 'accept');
        \Illuminate\Support\Carbon::setTestNow();

        $mock = Mockery::mock(LeadAcceptanceService::class);
        $this->app->instance(LeadAcceptanceService::class, $mock);

        $this->postJson('/api/v1/whatsapp/inbound/button-tap', $this->payload($oldToken))
            ->assertOk()
            ->assertJsonPath('data.reason', 'expired');
    }
}
