<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RankingCandidatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function asN8n(): void
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['n8n-service']);
    }

    private function makeProvider(array $overrides = []): User
    {
        $u = User::factory()->create(['role' => 'provider', 'status' => 'active']);
        $u->assignRole('provider');
        ProviderProfile::factory()->create(array_merge([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'acceptance_rate'     => 70,
            'rating_avg'          => 4.5,
        ], $overrides));
        ProviderService::create(['user_id' => $u->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $u->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $u->fresh(['providerProfile', 'providerServices', 'providerCoverage']);
    }

    public function test_requires_auth(): void
    {
        $this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16')
            ->assertStatus(401);
    }

    public function test_returns_enriched_candidates_with_wilaya_name(): void
    {
        $this->asN8n();
        $p = $this->makeProvider();

        $resp = $this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16');
        $resp->assertOk()
            ->assertJsonPath('data.service_type', 'photo')
            ->assertJsonPath('data.wilaya_code', '16')
            ->assertJsonPath('data.wilaya_name', 'Alger');

        $body = $resp->json('data');
        $this->assertCount(1, $body['candidates']);
        $this->assertSame($p->id, $body['candidates'][0]['id']);
        $this->assertSame('same_wilaya', $body['candidates'][0]['distance_from_couple']);
        $this->assertSame('Alger', $body['candidates'][0]['primary_wilaya_name']);
    }

    public function test_exclude_ids_accepts_array_and_csv_string(): void
    {
        $this->asN8n();
        $a = $this->makeProvider();
        $b = $this->makeProvider();

        $ids = array_column($this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16')->json('data.candidates'), 'id');
        $this->assertContains($a->id, $ids);
        $this->assertContains($b->id, $ids);

        $idsAfter = array_column($this->getJson("/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16&exclude_ids={$a->id}")->json('data.candidates'), 'id');
        $this->assertNotContains($a->id, $idsAfter);
        $this->assertContains($b->id, $idsAfter);
    }

    public function test_ordering_prefers_higher_acceptance_rate(): void
    {
        $this->asN8n();
        $low = $this->makeProvider(['acceptance_rate' => 30, 'rating_avg' => 3.0]);
        $high = $this->makeProvider(['acceptance_rate' => 95, 'rating_avg' => 4.8]);

        $ids = array_column($this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16')->json('data.candidates'), 'id');
        $this->assertSame($high->id, $ids[0]);
        $this->assertSame($low->id, $ids[1]);
    }

    public function test_limit_capped_at_50(): void
    {
        $this->asN8n();

        $this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16&limit=9999')
            ->assertStatus(422);
    }

    public function test_excludes_unverified(): void
    {
        $this->asN8n();
        $verified = $this->makeProvider(['verified' => true]);
        $unverified = $this->makeProvider(['verified' => false]);

        $ids = array_column($this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=16')->json('data.candidates'), 'id');
        $this->assertContains($verified->id, $ids);
        $this->assertNotContains($unverified->id, $ids);
    }

    public function test_classifies_adjacent_vs_far(): void
    {
        $this->asN8n();
        // Couple in Blida (09); neighbours per config include Alger (16)
        $adjacent = $this->makeProvider(['primary_wilaya_code' => '16']);
        ProviderCoverage::create(['user_id' => $adjacent->id, 'wilaya_code' => '09', 'travel_fee_dzd' => 1500]);

        $farProfile = $this->makeProvider(['primary_wilaya_code' => '31']);
        ProviderCoverage::create(['user_id' => $farProfile->id, 'wilaya_code' => '09', 'travel_fee_dzd' => 5000]);

        $body = $this->getJson('/api/v1/providers/ranking-candidates?service_type=photo&wilaya_code=09')->json('data.candidates');
        $byId = collect($body)->keyBy('id');

        $this->assertSame('adjacent', $byId[$adjacent->id]['distance_from_couple']);
        $this->assertSame('far',      $byId[$farProfile->id]['distance_from_couple']);
    }
}
