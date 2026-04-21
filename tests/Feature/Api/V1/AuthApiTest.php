<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_health_is_public(): void
    {
        $this->getJson('/api/v1/health')->assertOk()->assertJsonPath('data.status', 'ok');
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/service-requests')->assertStatus(401);
    }

    public function test_valid_token_with_correct_ability_succeeds(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['n8n-service']);

        $this->getJson('/api/v1/service-requests')->assertOk();
    }

    public function test_token_without_ability_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['some-other-ability']);

        $this->getJson('/api/v1/service-requests')->assertStatus(403);
    }
}
