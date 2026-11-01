<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Livewire\CoupleRequestForm;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\Setting;
use App\Models\UnlockPack;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * End-to-end journeys for the three audiences. These exercise the features
 * added in this round: conditional demande form, verification gating on
 * purchases, admin payment configuration + offline versement verification,
 * and admin manual round-robin assignment.
 */
class JourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function makeProvider(bool $verified = true, int $credits = 0, string $service = 'photo', string $city = 'NY'): User
    {
        $user = User::factory()->create([
            'email'    => 'p' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => '+12125554567',
            'whatsapp' => '+12125554567',
        ]);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'        => $user->id,
            'primary_city_code' => $city,
            'verified'       => $verified,
            'verified_at'    => $verified ? now()->subDay() : null,
            'business_type'  => 'sole_proprietor',
            'price_tier'     => 'mid',
            'credit_balance' => $credits,
        ]);
        ProviderService::create(['user_id' => $user->id, 'service_type' => $service]);
        ProviderCoverage::create(['user_id' => $user->id, 'city_code' => $city, 'travel_fee' => 0]);

        return $user->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    // ─── Demandeur journey ───────────────────────────────────

    public function test_demandeur_photo_only_submits_without_guest_count(): void
    {
        // A couple needing only a photographer: guest count is not required.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Yasmine')
            ->set('couple_whatsapp', '+12125552233')
            ->set('couple_city_code', 'NY')
            ->set('budget_range', 'from_20k_to_35k')
            ->set('services_needed', ['photo'])
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasNoErrors();

        $wr = WedRequest::first();
        $this->assertNotNull($wr);
        $this->assertNull($wr->guest_count, 'guest count should be null for photo-only');
        $this->assertSame(1, ServiceRequest::count());
    }

    public function test_demandeur_catering_requires_guest_count(): void
    {
        // Selecting catering makes guest count required — omitting it fails.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+12125554567')
            ->set('couple_city_code', 'NY')
            ->set('budget_range', 'from_20k_to_35k')
            ->set('services_needed', ['catering'])
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('guest_count', '')
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasErrors('guest_count');

        $this->assertSame(0, WedRequest::count());
    }

    public function test_demandeur_wedding_date_is_required(): void
    {
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+12125554567')
            ->set('couple_city_code', 'NY')
            ->set('budget_range', 'from_20k_to_35k')
            ->set('services_needed', ['photo'])
            ->set('wedding_date', null)
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasErrors('wedding_date');
    }

    // ─── Prestataire journey ─────────────────────────────────

    public function test_prestataire_unverified_cannot_buy_credits(): void
    {
        $provider = $this->makeProvider(verified: false);
        $pack = UnlockPack::factory()->create(['slug' => 'pack-x', 'active' => true, 'unlocks_count' => 6, 'price' => 99]);
        Setting::set('payments.online_enabled', '1', 'bool');

        $this->actingAs($provider)
            ->post(route('provider.credits.checkout', $pack->slug))
            ->assertRedirect(route('provider.credits.index'));

        // No purchase created for an unverified provider.
        $this->assertSame(0, \App\Models\Purchase::count());
    }

    public function test_prestataire_offline_versement_awaits_admin_then_grants_credits(): void
    {
        $provider = $this->makeProvider(verified: true, credits: 0);
        $pack = UnlockPack::factory()->create(['slug' => 'pack-ess', 'active' => true, 'unlocks_count' => 6, 'price' => 99]);
        Setting::set('payments.offline_enabled', '1', 'bool');

        // Provider declares an offline versement.
        $this->actingAs($provider)
            ->post(route('provider.credits.offline', $pack->slug), ['payment_reference' => 'VIR-123'])
            ->assertRedirect();

        $purchase = \App\Models\Purchase::first();
        $this->assertNotNull($purchase);
        $this->assertSame('pending_verification', $purchase->status);
        $this->assertSame(0, (int) $provider->providerProfile->fresh()->credit_balance, 'no credits before admin confirms');

        // Admin confirms → credits granted.
        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post(route('admin.purchases.confirm_offline', $purchase->purchase_uid))
            ->assertRedirect();

        $this->assertSame('paid', $purchase->fresh()->status);
        $this->assertSame(6, (int) $provider->providerProfile->fresh()->credit_balance);
    }

    // ─── Super-admin journey ─────────────────────────────────

    public function test_admin_configures_payment_methods(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.payments.update'), [
                'online_enabled'  => '1',
                'offline_enabled' => '1',
                'bank_name'       => 'BNA',
                'account_holder'  => 'Wedding Matching App SARL',
                'secondary_account_number'      => '0012345678',
            ])
            ->assertRedirect();

        $this->assertTrue((bool) Setting::get('payments.offline_enabled'));
        $this->assertSame('BNA', Setting::get('payments.offline.bank_name'));
    }

    public function test_admin_manually_assigns_verified_provider_silently(): void
    {
        $admin = $this->makeAdmin();
        // Provider covers city 16; request is in city 31 (outside coverage).
        $provider = $this->makeProvider(verified: true, service: 'photo', city: 'NY');
        $wr = WedRequest::factory()->create(['couple_city_code' => 'LA', 'services_needed' => ['photo']]);
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => RequestStatus::New->value,
            'current_round'  => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.service_requests.assign', $sr->service_uid), ['provider_id' => $provider->id])
            ->assertRedirect();

        $sr->refresh();
        $this->assertSame(RequestStatus::Round1->value, $sr->status->value);
        $this->assertContains($provider->id, (array) $sr->round_1_provider_ids);

        // The manually-assigned provider can now see the lead despite being
        // outside coverage.
        $canView = app(\App\Services\Matching\LeadEligibilityService::class)
            ->canViewLead($provider->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $sr->fresh());
        $this->assertTrue($canView);
    }

    public function test_admin_edits_demande_fields(): void
    {
        $admin = $this->makeAdmin();
        $wr = WedRequest::factory()->create(['couple_first_name' => 'Old', 'couple_city_code' => 'NY']);

        $this->actingAs($admin)
            ->put(route('admin.requests.update', $wr->wed_uid), [
                'couple_first_name'  => 'Nouveau',
                'couple_whatsapp'    => '+12125554567',
                'couple_city_code' => 'NY',
                'wedding_date'       => now()->addMonths(3)->toDateString(),
                'budget_range'       => 'from_20k_to_35k',
                'status'             => $wr->status?->value ?? 'new',
                'services_needed'    => (array) $wr->services_needed ?: ['photo'],
            ])
            ->assertRedirect(route('admin.requests.show', $wr->wed_uid));

        $this->assertSame('Nouveau', $wr->fresh()->couple_first_name);
    }
}
