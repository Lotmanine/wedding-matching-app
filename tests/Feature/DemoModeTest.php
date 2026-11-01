<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UnlockPackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['wedding.demo.enabled' => true]);
        $this->seed([CitySeeder::class, RolesSeeder::class, SettingsSeeder::class, UnlockPackSeeder::class, DemoSeeder::class]);
    }

    private function account(string $key): User
    {
        return User::where('email', config("wedding.demo.accounts.{$key}.email"))->firstOrFail();
    }

    public function test_login_pages_show_the_demo_accounts(): void
    {
        $this->get('/provider/login')->assertOk()->assertSee(config('wedding.demo.accounts.provider.email'));
        $this->get('/admin/login')->assertOk()->assertSee(config('wedding.demo.accounts.admin.email'));
    }

    public function test_login_pages_hide_them_outside_demo_mode(): void
    {
        config(['wedding.demo.enabled' => false]);

        $this->get('/provider/login')->assertOk()->assertDontSee(config('wedding.demo.accounts.provider.email'));
    }

    public function test_portfolio_notice_is_on_public_pages_in_demo_mode(): void
    {
        foreach (['/', '/for-providers', '/provider/login', '/admin/login'] as $url) {
            $this->get($url)->assertOk()->assertSee('id="portfolio-notice"', false);
        }

        config(['wedding.demo.enabled' => false]);

        $this->get('/')->assertOk()->assertDontSee('id="portfolio-notice"', false);
    }

    public function test_demo_provider_sees_requests_in_every_state(): void
    {
        $provider = $this->account('provider');

        $this->actingAs($provider)->get('/provider/leads')->assertOk()->assertSee('New York');
        $this->assertSame(1, ServiceRequest::where('unlocked_by_user_id', $provider->id)->count());
    }

    public function test_demo_checkout_grants_credits_without_a_gateway(): void
    {
        $provider = $this->account('provider');
        $before = (int) $provider->providerProfile->credit_balance;

        $this->actingAs($provider)
            ->post('/provider/credits/checkout/starter')
            ->assertRedirectContains('/provider/credits/success');

        $this->assertSame($before + 3, (int) $provider->providerProfile->fresh()->credit_balance);
        $this->assertSame('demo', Purchase::latest('id')->first()->gateway);
    }

    public function test_demo_admin_cannot_change_integrations_or_read_webhook_secrets(): void
    {
        $admin = $this->account('admin');

        $this->actingAs($admin)->get('/admin/webhooks')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->put('/admin/settings', ['credits_unlock_cost' => 9])->assertSessionHas('error');
        $this->actingAs($admin)->put('/admin/whatsapp', [])->assertSessionHas('error');

        $this->assertSame('1', (string) Setting::get('credits.unlock_cost'));
    }

    public function test_demo_admin_can_use_the_back_office(): void
    {
        $this->actingAs($this->account('admin'))->get('/admin')->assertOk();
    }

    public function test_reset_erases_visitor_activity(): void
    {
        $visitor = User::factory()->create(['role' => 'provider', 'email' => 'visitor@example.org']);
        $provider = $this->account('provider');
        $provider->providerProfile->update(['credit_balance' => 0]);

        $this->artisan('wedding:demo-reset')->assertSuccessful();

        $this->assertNull(User::find($visitor->id));
        $this->assertGreaterThan(0, (int) $this->account('provider')->providerProfile->credit_balance);
    }

    public function test_reset_refuses_to_run_outside_demo_mode(): void
    {
        config(['wedding.demo.enabled' => false]);

        $this->artisan('wedding:demo-reset')->assertFailed();
    }
}
