<?php

declare(strict_types=1);

namespace Tests\Feature\Matching;

use App\Enums\RequestStatus;
use App\Events\LeadAccepted;
use App\Models\CreditLedger;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\Results\AcceptResult;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(int $credits = 5, bool $verified = true): User
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
            'user_id'             => $user->id,
            'primary_city_code' => 'NY',
            'verified'            => $verified,
            'verified_at'         => $verified ? now()->subDays(5) : null,
            'languages'           => ['fr'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
            'credit_balance'      => $credits,
            'leads_received'      => 0,
            'leads_accepted'      => 0,
            'leads_refused'       => 0,
        ]);

        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'city_code' => 'NY', 'travel_fee' => 0]);

        return $user->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    private function makeLead(array $roundIds = []): ServiceRequest
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

    public function test_successful_accept_deducts_credits_sets_unlocked_fires_event(): void
    {
        Event::fake([LeadAccepted::class]);

        $user = $this->makeProvider(credits: 3);
        $lead = $this->makeLead([$user->id]);

        /** @var LeadAcceptanceService $svc */
        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_SUCCESS, $result->status);

        $lead->refresh();
        $this->assertSame($user->id, $lead->unlocked_by_user_id);
        $this->assertSame(RequestStatus::Accepted, $lead->status);
        $this->assertSame(1, $lead->credits_charged);
        $this->assertNotNull($lead->unlocked_at);

        $user->refresh()->load('providerProfile');
        $this->assertSame(2, $user->providerProfile->credit_balance);
        $this->assertSame(1, $user->providerProfile->leads_accepted);

        $this->assertSame(1, CreditLedger::where('user_id', $user->id)->where('type', 'unlock')->count());

        Event::assertDispatched(LeadAccepted::class);
    }

    public function test_double_accept_returns_already_taken_on_second_call(): void
    {
        $first = $this->makeProvider(credits: 3);
        $second = $this->makeProvider(credits: 3);

        $lead = $this->makeLead([$first->id, $second->id]);

        $svc = app(LeadAcceptanceService::class);
        $r1 = $svc->accept($first->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);
        $r2 = $svc->accept($second->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_SUCCESS, $r1->status);
        $this->assertSame(AcceptResult::STATUS_ALREADY_TAKEN, $r2->status);

        $second->refresh()->load('providerProfile');
        $this->assertSame(3, $second->providerProfile->credit_balance, 'losing provider must not be charged');
    }

    public function test_insufficient_credits_returns_result_and_does_not_unlock(): void
    {
        $user = $this->makeProvider(credits: 0);
        $lead = $this->makeLead([$user->id]);

        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_INSUFFICIENT_CREDITS, $result->status);

        $lead->refresh();
        $this->assertNull($lead->unlocked_by_user_id);
        $this->assertSame(0, CreditLedger::where('user_id', $user->id)->count());
    }

    public function test_expired_lead_cannot_be_accepted(): void
    {
        $user = $this->makeProvider();
        $lead = $this->makeLead([$user->id]);
        $lead->forceFill(['expires_at' => now()->subDay()])->save();

        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_EXPIRED, $result->status);
        $this->assertNull($lead->fresh()->unlocked_by_user_id);
    }

    public function test_out_of_coverage_lead_rejected_when_not_assigned(): void
    {
        // A provider who covers city 16 must NOT be able to accept a lead in
        // city 31 that they were never placed in a round for (open marketplace).
        $user = $this->makeProvider(); // offers photo, covers 16
        $wr = WedRequest::factory()->create([
            'couple_city_code' => 'LA', // outside coverage
            'services_needed'    => ['photo'],
        ]);
        $lead = ServiceRequest::create([
            'wed_request_id'   => $wr->id,
            'service_type'     => 'photo',
            'status'           => RequestStatus::OpenMarketplace->value,
            'current_round'    => 4,
            'expires_at'       => now()->addHour(),
        ]);

        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_NOT_ELIGIBLE, $result->status);
    }

    public function test_manually_assigned_provider_can_accept_outside_coverage(): void
    {
        // Manual round-robin: an admin assigns a verified provider to a lead
        // outside their coverage. Being placed in a round list overrides the
        // geographic filter, so they can accept it.
        $user = $this->makeProvider(); // offers photo, covers 16
        $wr = WedRequest::factory()->create([
            'couple_city_code' => 'LA', // outside coverage
            'services_needed'    => ['photo'],
        ]);
        $lead = ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => RequestStatus::Round1->value,
            'current_round'        => 1,
            'round_started_at'     => now(),
            'expires_at'           => now()->addHour(),
            'round_1_provider_ids' => [$user->id],
        ]);

        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_SUCCESS, $result->status);
    }

    public function test_unverified_provider_cannot_accept_even_if_assigned(): void
    {
        // Verification is still required even for a manually-assigned lead.
        $user = $this->makeProvider(verified: false); // covers 16, NOT verified
        $wr = WedRequest::factory()->create([
            'couple_city_code' => 'NY',
            'services_needed'    => ['photo'],
        ]);
        $lead = ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => RequestStatus::Round1->value,
            'current_round'        => 1,
            'round_started_at'     => now(),
            'expires_at'           => now()->addHour(),
            'round_1_provider_ids' => [$user->id],
        ]);

        $svc = app(LeadAcceptanceService::class);
        $result = $svc->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertSame(AcceptResult::STATUS_NOT_ELIGIBLE, $result->status);
    }

    public function test_cache_flushed_for_all_round_participants(): void
    {
        $p1 = $this->makeProvider();
        $p2 = $this->makeProvider();
        $lead = $this->makeLead([$p1->id, $p2->id]);

        Cache::put("leads:count:{$p1->id}:v1", 99, 60);
        Cache::put("leads:count:{$p2->id}:v1", 99, 60);
        Cache::put('leads:open_marketplace:version', '1', now()->addHour());

        $svc = app(LeadAcceptanceService::class);
        $svc->accept($p1->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $this->assertNotSame('1', Cache::get('leads:open_marketplace:version'));
    }

    public function test_accept_endpoint_hits_controller_and_unlocks(): void
    {
        $user = $this->makeProvider(credits: 2);
        $lead = $this->makeLead([$user->id]);

        $this->actingAs($user)
            ->post(route('provider.leads.accept', $lead->service_uid))
            ->assertRedirect(route('provider.leads.show', $lead->service_uid));

        $this->assertSame($user->id, $lead->fresh()->unlocked_by_user_id);
    }

    public function test_accepted_view_shows_full_contact(): void
    {
        $user = $this->makeProvider(credits: 2);
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Fatima',
            'couple_whatsapp'    => '+12125553456',
            'couple_city_code' => 'NY',
            'services_needed'    => ['photo'],
        ]);
        $lead = ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => RequestStatus::Round1->value,
            'current_round'        => 1,
            'round_started_at'     => now(),
            'round_1_provider_ids' => [$user->id],
        ]);

        app(LeadAcceptanceService::class)
            ->accept($user->fresh(['providerProfile', 'providerServices', 'providerCoverage']), $lead);

        $response = $this->actingAs($user)->get(route('provider.leads.show', $lead->service_uid));
        $response->assertOk();
        $response->assertSee('Fatima');
        $response->assertSee('+12125553456');
    }
}
