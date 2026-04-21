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

class ProviderApiTest extends TestCase
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
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['n8n-service']);
    }

    private function makeProvider(bool $verified = true, string $wilaya = '16', string $service = 'photo'): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => '+213661234567',
            'whatsapp' => '+213661234567',
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => $wilaya,
            'verified'            => $verified,
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'acceptance_rate'     => 50,
            'rating_avg'          => 4.5,
        ]);
        ProviderService::create(['user_id' => $u->id, 'service_type' => $service]);
        ProviderCoverage::create(['user_id' => $u->id, 'wilaya_code' => $wilaya, 'travel_fee_dzd' => 0]);

        return $u;
    }

    public function test_eligible_returns_only_verified_and_active(): void
    {
        $this->asN8n();
        $ok = $this->makeProvider(verified: true);
        $unverified = $this->makeProvider(verified: false);

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&wilaya_code=16');
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

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&wilaya_code=16&exclude_ids[]=' . $a->id);
        $ids = array_column($resp->json('data'), 'id');
        $this->assertNotContains($a->id, $ids);
        $this->assertContains($b->id, $ids);
    }

    public function test_eligible_respects_service_and_wilaya_filters(): void
    {
        $this->asN8n();
        $photoAlger = $this->makeProvider(wilaya: '16', service: 'photo');
        $traiteurOran = $this->makeProvider(wilaya: '31', service: 'traiteur');

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&wilaya_code=16');
        $ids = array_column($resp->json('data'), 'id');
        $this->assertContains($photoAlger->id, $ids);
        $this->assertNotContains($traiteurOran->id, $ids);
    }

    public function test_response_excludes_email_and_phone(): void
    {
        $this->asN8n();
        $p = $this->makeProvider();

        $resp = $this->getJson('/api/v1/providers/eligible?service_type=photo&wilaya_code=16');
        $body = $resp->getContent();
        $this->assertStringNotContainsString($p->email, $body, 'Email must not leak via API');
        $this->assertStringNotContainsString((string) $p->phone, $body, 'Phone must not leak via API');
    }
}
