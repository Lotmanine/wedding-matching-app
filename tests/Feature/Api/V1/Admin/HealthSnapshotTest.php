<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HealthSnapshotTest extends TestCase
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
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['n8n-service']);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->getJson('/api/v1/admin/ops/health-snapshot')->assertStatus(401);
    }

    public function test_wrong_ability_returns_403(): void
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['nope']);

        $this->getJson('/api/v1/admin/ops/health-snapshot')->assertStatus(403);
    }

    public function test_returns_snapshot_envelope(): void
    {
        $this->asN8n();

        $this->getJson('/api/v1/admin/ops/health-snapshot')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'severity',
                    'run_at',
                    'stuck_matching',
                    'failed_webhooks_24h',
                    'failed_whatsapp_24h',
                    'n8n_errors_24h',
                ],
                'meta' => ['timestamp', 'api_version'],
            ])
            ->assertJsonPath('data.severity', 'none');
    }

    public function test_run_at_is_iso8601(): void
    {
        $this->asN8n();

        $runAt = $this->getJson('/api/v1/admin/ops/health-snapshot')->json('data.run_at');
        $this->assertIsString($runAt);
        $this->assertTrue(strtotime($runAt) !== false);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $runAt);
    }
}
