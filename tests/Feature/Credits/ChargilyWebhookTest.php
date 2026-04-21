<?php

declare(strict_types=1);

namespace Tests\Feature\Credits;

use App\Events\CreditsGranted;
use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChargilyWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        Config::set('zawaj.chargily.webhook_secret', 'test-webhook-secret');
    }

    private function seedPendingPurchase(int $balance = 0, string $checkoutId = 'chk_abc'): Purchase
    {
        $user = User::factory()->create(['role' => 'provider']);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'credit_balance'      => $balance,
        ]);

        $pack = UnlockPack::create([
            'name' => 'Pack Essentiel', 'slug' => 'pack-essentiel-' . uniqid(),
            'unlocks_count' => 6, 'price_dzd' => 7650, 'is_popular' => true, 'active' => true, 'sort_order' => 2,
        ]);

        return Purchase::create([
            'user_id'             => $user->id,
            'unlock_pack_id'      => $pack->id,
            'amount_dzd'          => $pack->price_dzd,
            'unlocks_count'       => $pack->unlocks_count,
            'status'              => 'pending',
            'gateway'             => 'chargily',
            'gateway_checkout_id' => $checkoutId,
        ]);
    }

    private function signedPost(array $body, string $signature = null): \Illuminate\Testing\TestResponse
    {
        $raw = json_encode($body, JSON_UNESCAPED_UNICODE);
        $signature = $signature ?? hash_hmac('sha256', $raw, 'test-webhook-secret');

        return $this->call(
            method: 'POST',
            uri: '/webhooks/chargily',
            server: [
                'HTTP_SIGNATURE'  => $signature,
                'CONTENT_TYPE'    => 'application/json',
            ],
            content: $raw,
        );
    }

    public function test_valid_signature_paid_event_grants_credits(): void
    {
        Event::fake([CreditsGranted::class]);

        $purchase = $this->seedPendingPurchase(balance: 0, checkoutId: 'chk_123');

        $this->signedPost([
            'type' => 'checkout.paid',
            'data' => ['id' => 'chk_123', 'payment_id' => 'pay_xyz'],
        ])->assertOk();

        $purchase->refresh();
        $this->assertSame('paid', $purchase->status);
        $this->assertNotNull($purchase->credit_ledger_id);
        $this->assertSame(1, CreditLedger::where('user_id', $purchase->user_id)->where('type', 'purchase')->count());

        $purchase->user->refresh()->load('providerProfile');
        $this->assertSame(6, $purchase->user->providerProfile->credit_balance);

        Event::assertDispatched(CreditsGranted::class);
    }

    public function test_invalid_signature_rejected_no_db_change(): void
    {
        $purchase = $this->seedPendingPurchase();

        $this->signedPost(
            ['type' => 'checkout.paid', 'data' => ['id' => $purchase->gateway_checkout_id]],
            signature: 'bogus-signature'
        )->assertStatus(401);

        $this->assertSame('pending', $purchase->fresh()->status);
        $this->assertSame(0, CreditLedger::count());
    }

    public function test_unknown_checkout_id_returns_404(): void
    {
        $this->signedPost([
            'type' => 'checkout.paid',
            'data' => ['id' => 'chk_does_not_exist'],
        ])->assertStatus(404);
    }

    public function test_double_fire_is_idempotent_credits_granted_once(): void
    {
        $purchase = $this->seedPendingPurchase(checkoutId: 'chk_idem');

        $payload = ['type' => 'checkout.paid', 'data' => ['id' => 'chk_idem', 'payment_id' => 'pay_1']];

        $this->signedPost($payload)->assertOk();
        $this->signedPost($payload)->assertOk();

        $this->assertSame(1, CreditLedger::where('user_id', $purchase->user_id)->where('type', 'purchase')->count());

        $purchase->user->refresh()->load('providerProfile');
        $this->assertSame(6, $purchase->user->providerProfile->credit_balance, 'Second webhook must not double-grant');
    }

    public function test_failed_event_marks_failed_no_credits(): void
    {
        $purchase = $this->seedPendingPurchase(checkoutId: 'chk_fail');

        $this->signedPost([
            'type' => 'checkout.failed',
            'data' => ['id' => 'chk_fail'],
        ])->assertOk();

        $purchase->refresh();
        $this->assertSame('failed', $purchase->status);
        $this->assertSame('gateway_failed', $purchase->failure_reason);
        $this->assertSame(0, CreditLedger::count());
    }

    public function test_cancelled_event_marks_cancelled(): void
    {
        $purchase = $this->seedPendingPurchase(checkoutId: 'chk_cancel');

        $this->signedPost([
            'type' => 'checkout.cancelled',
            'data' => ['id' => 'chk_cancel'],
        ])->assertOk();

        $this->assertSame('cancelled', $purchase->fresh()->status);
    }
}
