<?php

declare(strict_types=1);

namespace Tests\Feature\Credits;

use App\Models\ProviderProfile;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use App\Services\Payments\ChargilyClient;
use App\Services\Payments\PurchaseService;
use Database\Seeders\RolesSeeder;
use Database\Seeders\UnlockPackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(UnlockPackSeeder::class);
    }

    private function makeProvider(): User
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
            'credit_balance'      => 0,
        ]);

        return $user->fresh('providerProfile');
    }

    public function test_initiate_creates_pending_purchase(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'pack-essentiel')->first();

        $purchase = app(PurchaseService::class)->initiate($user, $pack);

        $this->assertSame('pending', $purchase->status);
        $this->assertSame(6, $purchase->unlocks_count);
        $this->assertSame(7650, $purchase->amount_dzd);
        $this->assertMatchesRegularExpression('/^PUR-\d{8}-\d{4}$/', $purchase->purchase_uid);
    }

    public function test_checkout_redirects_to_chargily_url(): void
    {
        $user = $this->makeProvider();

        $mock = Mockery::mock(ChargilyClient::class);
        $mock->shouldReceive('createCheckout')
            ->once()
            ->andReturn([
                'id'           => 'chk_mock_123',
                'checkout_url' => 'https://pay.chargily.net/checkout/chk_mock_123',
                'raw'          => ['id' => 'chk_mock_123'],
            ]);
        $this->app->instance(ChargilyClient::class, $mock);

        $response = $this->actingAs($user)->post(route('provider.credits.checkout', 'pack-essentiel'));

        $response->assertRedirect('https://pay.chargily.net/checkout/chk_mock_123');

        $purchase = Purchase::where('user_id', $user->id)->first();
        $this->assertNotNull($purchase);
        $this->assertSame('chk_mock_123', $purchase->gateway_checkout_id);
    }

    public function test_success_page_shows_pending_before_webhook(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'pack-decouverte')->first();
        $purchase = app(PurchaseService::class)->initiate($user, $pack);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertOk()
            ->assertSee('cours de confirmation');
    }

    public function test_success_page_shows_paid_after_markPaid(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'pack-decouverte')->first();
        $svc = app(PurchaseService::class);
        $purchase = $svc->initiate($user, $pack);
        $svc->markPaid($purchase, ['id' => 'chk_x', 'payment_id' => 'pay_x']);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertOk()
            ->assertSee('Paiement confirmé');
    }

    public function test_success_page_403_on_other_users_purchase(): void
    {
        $user = $this->makeProvider();
        $other = $this->makeProvider();

        $pack = UnlockPack::where('slug', 'pack-decouverte')->first();
        $purchase = app(PurchaseService::class)->initiate($other, $pack);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertNotFound();
    }

    public function test_checkout_throws_404_for_inactive_pack(): void
    {
        $user = $this->makeProvider();
        UnlockPack::where('slug', 'pack-decouverte')->update(['active' => false]);

        $this->actingAs($user)
            ->post(route('provider.credits.checkout', 'pack-decouverte'))
            ->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
