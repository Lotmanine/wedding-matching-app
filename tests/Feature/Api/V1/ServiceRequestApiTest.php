<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\RequestStatus;
use App\Models\AuditLog;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceRequestApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function asN8n(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['n8n-service']);

        return $user;
    }

    private function makeEligibleProvider(int $balance = 5): User
    {
        $u = User::factory()->create(['role' => 'provider', 'status' => 'active']);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'credit_balance'      => $balance,
        ]);
        ProviderService::create(['user_id' => $u->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $u->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $u->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    private function makeLead(array $roundIds = [], string $status = 'round1'): ServiceRequest
    {
        $wr = WedRequest::factory()->create(['couple_wilaya_code' => '16', 'services_needed' => ['photo']]);

        return ServiceRequest::create([
            'wed_request_id'       => $wr->id,
            'service_type'         => 'photo',
            'status'               => $status,
            'current_round'        => $status === 'round1' ? 1 : 0,
            'round_started_at'     => now(),
            'round_1_provider_ids' => $roundIds,
        ]);
    }

    public function test_index_returns_collection(): void
    {
        $this->asN8n();
        $this->makeLead();

        $resp = $this->getJson('/api/v1/service-requests?limit=10');
        $resp->assertOk()->assertJsonStructure(['data' => [['service_uid', 'status']], 'meta']);
    }

    public function test_pending_matching_returns_only_matching_status(): void
    {
        $this->asN8n();
        $this->makeLead(status: 'round1');
        $this->makeLead(status: 'matching');

        $resp = $this->getJson('/api/v1/service-requests/pending-matching');
        $resp->assertOk();
        foreach ($resp->json('data') as $row) {
            $this->assertSame('matching', $row['status']);
        }
    }

    public function test_start_round_transitions_state_and_logs(): void
    {
        $this->asN8n();
        $sr = $this->makeLead(status: 'matching');
        $p1 = $this->makeEligibleProvider();
        $p2 = $this->makeEligibleProvider();

        $resp = $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/start-round', [
            'round'         => 1,
            'provider_ids'  => [$p1->id, $p2->id],
        ]);
        $resp->assertOk()->assertJsonPath('data.status', 'round1');

        $sr->refresh();
        $this->assertSame(1, $sr->current_round);
        $this->assertEqualsCanonicalizing([$p1->id, $p2->id], (array) $sr->round_1_provider_ids);
        $this->assertTrue(AuditLog::where('action', 'api.service_request.start_round')->exists());
    }

    public function test_start_round_on_already_advanced_returns_409(): void
    {
        $this->asN8n();
        $sr = $this->makeLead(status: 'round2');
        $sr->forceFill(['current_round' => 2])->save();
        $p = $this->makeEligibleProvider();

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/start-round', [
            'round'        => 1,
            'provider_ids' => [$p->id],
        ])->assertStatus(409)->assertJsonPath('error.code', 'already_advanced');
    }

    public function test_start_round_invalid_round_returns_422(): void
    {
        $this->asN8n();
        $sr = $this->makeLead();
        $p = $this->makeEligibleProvider();

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/start-round', [
            'round'        => 5,
            'provider_ids' => [$p->id],
        ])->assertStatus(422);
    }

    public function test_accept_with_sufficient_credits_returns_200(): void
    {
        $this->asN8n();
        $p = $this->makeEligibleProvider(balance: 3);
        $sr = $this->makeLead([$p->id]);

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/accept', [
            'provider_id' => $p->id,
            'via'         => 'api',
        ])->assertOk()->assertJsonPath('data.status', 'success');

        $this->assertSame($p->id, $sr->fresh()->unlocked_by_user_id);
    }

    public function test_accept_insufficient_credits_returns_402(): void
    {
        $this->asN8n();
        $p = $this->makeEligibleProvider(balance: 0);
        $sr = $this->makeLead([$p->id]);

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/accept', [
            'provider_id' => $p->id,
            'via'         => 'api',
        ])->assertStatus(402)->assertJsonPath('error.code', 'insufficient_credits');
    }

    public function test_accept_already_taken_returns_409(): void
    {
        $this->asN8n();
        $first = $this->makeEligibleProvider(balance: 3);
        $second = $this->makeEligibleProvider(balance: 3);
        $sr = $this->makeLead([$first->id, $second->id]);

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/accept', ['provider_id' => $first->id, 'via' => 'api'])->assertOk();
        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/accept', ['provider_id' => $second->id, 'via' => 'api'])
            ->assertStatus(409)->assertJsonPath('error.code', 'already_taken');
    }

    public function test_refuse_adds_to_refused_list(): void
    {
        $this->asN8n();
        $p = $this->makeEligibleProvider();
        $sr = $this->makeLead([$p->id]);

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/refuse', [
            'provider_id' => $p->id,
        ])->assertOk();

        $this->assertContains($p->id, (array) $sr->fresh()->refused_by_provider_ids);
    }

    public function test_force_open_transitions_to_marketplace(): void
    {
        $this->asN8n();
        $sr = $this->makeLead(status: 'round2');

        $this->postJson('/api/v1/service-requests/' . $sr->service_uid . '/force-open')
            ->assertOk()->assertJsonPath('data.status', 'open_marketplace');

        $this->assertSame(RequestStatus::OpenMarketplace, $sr->fresh()->status);
    }

    public function test_show_returns_full_resource(): void
    {
        $this->asN8n();
        $sr = $this->makeLead();

        $this->getJson('/api/v1/service-requests/' . $sr->service_uid)
            ->assertOk()
            ->assertJsonPath('data.service_uid', $sr->service_uid)
            ->assertJsonStructure(['data' => ['service_uid', 'status', 'current_round', 'wed_request']]);
    }
}
