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
            'primary_city_code' => 'NY',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
            'credit_balance'      => 0,
        ]);

        return $user->fresh('providerProfile');
    }

    public function test_initiate_creates_pending_purchase(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'essential')->first();

        $purchase = app(PurchaseService::class)->initiate($user, $pack);

        $this->assertSame('pending', $purchase->status);
        $this->assertSame(6, $purchase->unlocks_count);
        $this->assertSame(99, $purchase->amount);
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

        $response = $this->actingAs($user)->post(route('provider.credits.checkout', 'essential'));

        $response->assertRedirect('https://pay.chargily.net/checkout/chk_mock_123');

        $purchase = Purchase::where('user_id', $user->id)->first();
        $this->assertNotNull($purchase);
        $this->assertSame('chk_mock_123', $purchase->gateway_checkout_id);
    }

    public function test_success_page_shows_pending_before_webhook(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'starter')->first();
        $purchase = app(PurchaseService::class)->initiate($user, $pack);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertOk()
            ->assertSee('being confirmed');
    }

    public function test_success_page_shows_paid_after_markPaid(): void
    {
        $user = $this->makeProvider();
        $pack = UnlockPack::where('slug', 'starter')->first();
        $svc = app(PurchaseService::class);
        $purchase = $svc->initiate($user, $pack);
        $svc->markPaid($purchase, ['id' => 'chk_x', 'payment_id' => 'pay_x']);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertOk()
            ->assertSee('Payment confirmed');
    }

    public function test_success_page_403_on_other_users_purchase(): void
    {
        $user = $this->makeProvider();
        $other = $this->makeProvider();

        $pack = UnlockPack::where('slug', 'starter')->first();
        $purchase = app(PurchaseService::class)->initiate($other, $pack);

        $this->actingAs($user)
            ->get(route('provider.credits.success') . '?purchase=' . $purchase->purchase_uid)
            ->assertNotFound();
    }

    public function test_checkout_throws_404_for_inactive_pack(): void
    {
        $user = $this->makeProvider();
        UnlockPack::where('slug', 'starter')->update(['active' => false]);

        $this->actingAs($user)
            ->post(route('provider.credits.checkout', 'starter'))
            ->assertNotFound();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
