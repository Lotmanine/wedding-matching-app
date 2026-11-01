<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function asN8n(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['n8n-service']);
    }

    private function makeProvider(bool $verified = true, string $city = 'NY', string $service = 'photo'): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => '+12125554567',
            'whatsapp' => '+12125554567',
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_city_code' => $city,
            'verified'            => $verified,
            'languages'           => ['fr'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
            'acceptance_rate'     => 50,
            'rating_avg'          => 4.5,
        ]);
        ProviderService::create(['user_id' => $u->id, 'service_type' => $service]);
        ProviderCoverage::create(['user_id' => $u->id, 'city_code' => $city, 'travel_fee' => 0]);

        return $u;
    }

    public function test_eligible_returns_only_verified_and_active(): void
    {
        $this->asN8n();
        $ok = $this->makeProvider(verified: true);
        $unverified = $this->makeProvider(verified: false);

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&city_code=NY');
        $resp->assertOk();
        $ids = array_column($resp->json('data'), 'id');
        $this->assertContains($ok->id, $ids);
        $this->assertNotContains($unverified->id, $ids);
    }

    public function test_eligible_excludes_specified_ids(): void
    {
        $this->asN8n();
        $a = $this->makeProvider();
        $b = $this->makeProvider();

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&city_code=NY&exclude_ids[]=' . $a->id);
        $ids = array_column($resp->json('data'), 'id');
        $this->assertNotContains($a->id, $ids);
        $this->assertContains($b->id, $ids);
    }

    public function test_eligible_respects_service_and_city_filters(): void
    {
        $this->asN8n();
        $photoNewYork = $this->makeProvider(city: 'NY', service: 'photo');
        $cateringLosAngeles = $this->makeProvider(city: 'LA', service: 'catering');

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&city_code=NY');
        $ids = array_column($resp->json('data'), 'id');
        $this->assertContains($photoNewYork->id, $ids);
        $this->assertNotContains($cateringLosAngeles->id, $ids);
    }

    public function test_response_excludes_email_and_phone(): void
    {
        $this->asN8n();
        $p = $this->makeProvider();

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&city_code=NY');
        $body = $resp->getContent();
        $this->assertStringNotContainsString($p->email, $body, 'Email must not leak via API');
        $this->assertStringNotContainsString((string) $p->phone, $body, 'Phone must not leak via API');
    }
}
