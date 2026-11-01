<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Enums\RequestStatus;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'email'    => 'pro' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => '+12125554567',
            'whatsapp' => '+12125554567',
        ], $overrides));
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_city_code' => 'NY',
            'verified'            => true,
            'verified_at'         => now()->subDays(10),
            'languages'           => ['fr'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
            'portfolio_images'    => [],
        ]);

        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'city_code' => 'NY', 'travel_fee' => 0]);

        return $user->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    private function makeLead(array $wrOverrides = [], array $srOverrides = []): ServiceRequest
    {
        $wr = WedRequest::factory()->create(array_merge([
            'couple_city_code' => 'NY',
            'services_needed'    => ['photo'],
        ], $wrOverrides));

        return ServiceRequest::create(array_merge([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => RequestStatus::Round1->value,
            'current_round'  => 1,
            'round_started_at' => now(),
        ], $srOverrides));
    }

    public function test_unverified_provider_sees_pending_state_and_no_leads(): void
    {
        $user = $this->makeProvider();
        $user->providerProfile->update(['verified' => false, 'verified_at' => null]);
        $this->makeLead([], ['round_1_provider_ids' => [$user->id]]);

        $response = $this->actingAs($user)->get('/provider/leads');
        $response->assertOk();
        $response->assertSee('under review');
    }

    public function test_verified_provider_sees_matching_lead(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([], ['round_1_provider_ids' => [$user->id]]);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertSee($lead->service_uid);
    }

    public function test_lead_with_different_service_hidden(): void
    {
        $user = $this->makeProvider(); // offers photo
        $this->makeLead(
            ['services_needed' => ['catering']],
            ['service_type' => 'catering', 'round_1_provider_ids' => [$user->id]]
        );

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText('SRV-');
    }

    public function test_lead_outside_coverage_hidden(): void
    {
        $user = $this->makeProvider(); // covers 16 only
        $this->makeLead(
            ['couple_city_code' => 'LA'], // Los Angeles
            ['round_1_provider_ids' => [$user->id]]
        );

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText('SRV-');
    }

    public function test_refused_lead_hidden(): void
    {
        $user = $this->makeProvider();
        $this->makeLead([], [
            'round_1_provider_ids'    => [$user->id],
            'refused_by_provider_ids' => [$user->id],
        ]);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText('SRV-');
    }

    public function test_unlocked_lead_hidden(): void
    {
        $other = User::factory()->create(['role' => 'provider']);
        $user = $this->makeProvider();
        $this->makeLead([], [
            'round_1_provider_ids' => [$user->id],
            'unlocked_by_user_id'  => $other->id,
            'unlocked_at'          => now(),
        ]);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText('SRV-');
    }

    public function test_expired_lead_hidden(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([], ['round_1_provider_ids' => [$user->id]]);
        $lead->forceFill(['expires_at' => now()->subDay()])->save();

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText($lead->service_uid);
    }

    public function test_round_2_lead_visible_to_round_1_provider(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([], [
            'status'                => RequestStatus::Round2->value,
            'current_round'         => 2,
            'round_1_provider_ids'  => [$user->id],
            'round_2_provider_ids'  => [999],
        ]);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertSee($lead->service_uid);
    }

    public function test_open_marketplace_visible_to_any_eligible_provider(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([], [
            'status'        => RequestStatus::OpenMarketplace->value,
            'current_round' => 4,
        ]);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertSee($lead->service_uid);
    }

    public function test_service_filter_narrows_results(): void
    {
        $user = $this->makeProvider();
        ProviderService::create(['user_id' => $user->id, 'service_type' => 'video']);

        $this->makeLead([], ['round_1_provider_ids' => [$user->id]]); // photo
        $v = $this->makeLead(
            ['services_needed' => ['video']],
            ['service_type' => 'video', 'round_1_provider_ids' => [$user->id]]
        );

        $response = $this->actingAs($user)->get('/provider/leads?service_type=video');
        $response->assertOk();
        $response->assertSee($v->service_uid);
    }

    public function test_couple_user_gets_403_on_leads_index(): void
    {
        $couple = User::factory()->create(['role' => 'couple']);
        $couple->assignRole('couple');

        $this->actingAs($couple)->get('/provider/leads')->assertForbidden();
    }

    public function test_lead_detail_hides_contact_info(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead(
            [
                'couple_first_name' => 'Fatima',
                'couple_whatsapp'   => '+12125553456',
            ],
            ['round_1_provider_ids' => [$user->id]]
        );

        $response = $this->actingAs($user)->get(route('provider.leads.show', $lead->service_uid));
        $response->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString('Fatima', $body, 'Full first name must not leak');
        $this->assertStringNotContainsString('+12125553456', $body, 'WhatsApp must not leak');
        $response->assertSee('F.');
    }

    public function test_lead_detail_403_when_not_eligible(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead(
            ['couple_city_code' => 'LA'], // outside coverage
            ['round_1_provider_ids' => [999]]
        );

        $this->actingAs($user)->get(route('provider.leads.show', $lead->service_uid))
            ->assertForbidden();
    }
}
