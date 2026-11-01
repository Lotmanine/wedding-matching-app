<?php

declare(strict_types=1);

namespace Tests\Feature\Matching;

use App\Enums\RequestStatus;
use App\Events\LeadRefused;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\Results\RefuseResult;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RefusalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(): User
    {
        $user = User::factory()->create([
            'email'  => 'p' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'role'   => 'provider',
            'status' => 'active',
        ]);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_city_code' => 'NY',
            'verified'            => true,
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
            'languages'           => ['fr'],
            'leads_refused'       => 0,
            'leads_received'      => 0,
            'leads_accepted'      => 0,
        ]);

        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'city_code' => 'NY', 'travel_fee' => 0]);

        return $user->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    private function makeLead(array $roundIds): ServiceRequest
    {
        $wr = WedRequest::factory()->create([
            'couple_city_code' => 'NY',
            'services_needed'    => ['photo'],
        ]);

        return ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => RequestStatus::Round1->value,
            'current_round'        => 1,
            'round_started_at'     => now(),
            'round_1_provider_ids' => $roundIds,
        ]);
    }

    public function test_refuse_adds_provider_to_refused_list_and_fires_event(): void
    {
        Event::fake([LeadRefused::class]);

        $user = $this->makeProvider();
        $lead = $this->makeLead([$user->id]);

        $result = app(LeadAcceptanceService::class)->refuse($user, $lead);

        $this->assertSame(RefuseResult::STATUS_SUCCESS, $result->status);
        $this->assertContains($user->id, (array) $lead->fresh()->refused_by_provider_ids);

        $user->refresh()->load('providerProfile');
        $this->assertSame(1, $user->providerProfile->leads_refused);

        Event::assertDispatched(LeadRefused::class);
    }

    public function test_refuse_is_idempotent(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([$user->id]);

        $svc = app(LeadAcceptanceService::class);
        $svc->refuse($user, $lead);
        $second = $svc->refuse($user, $lead);

        $this->assertSame(RefuseResult::STATUS_ALREADY_REFUSED, $second->status);

        $user->refresh()->load('providerProfile');
        $this->assertSame(1, $user->providerProfile->leads_refused, 'refused counter should not double');
    }

    public function test_refused_provider_no_longer_sees_lead(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([$user->id]);

        app(LeadAcceptanceService::class)->refuse($user, $lead);

        $this->actingAs($user)->get('/provider/leads')
            ->assertOk()
            ->assertDontSeeText($lead->service_uid);
    }

    public function test_refuse_endpoint_redirects_to_index(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([$user->id]);

        $this->actingAs($user)
            ->post(route('provider.leads.refuse', $lead->service_uid))
            ->assertRedirect(route('provider.leads.index'));
    }
}
