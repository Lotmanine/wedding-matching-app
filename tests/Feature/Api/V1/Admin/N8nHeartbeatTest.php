<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\N8nHeartbeat;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class N8nHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function asN8n(): void
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['n8n-service']);
    }

    private function payload(string $severity = 'none'): array
    {
        return [
            'run_at'           => now()->toAtomString(),
            'severity'         => $severity,
            'snapshot_summary' => [
                'stuck_matching'      => 0,
                'failed_webhooks_24h' => 0,
                'failed_whatsapp_24h' => 0,
                'n8n_errors_24h'      => 0,
            ],
            'alerts_sent' => ['email' => false, 'telegram' => false],
        ];
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/v1/webhooks/n8n/heartbeat', $this->payload())->assertStatus(401);
    }

    public function test_persists_a_row(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/webhooks/n8n/heartbeat', $this->payload('warn'))
            ->assertOk()
            ->assertJsonPath('data.status', 'recorded');

        $this->assertSame(1, N8nHeartbeat::count());
        $row = N8nHeartbeat::first();
        $this->assertSame('warn', $row->severity);
    }

    public function test_validation_rejects_unknown_severity(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/webhooks/n8n/heartbeat', $this->payload('panic'))
            ->assertStatus(422);
    }

    public function test_critical_with_alerts_sent_persists_alert_flags(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/webhooks/n8n/heartbeat', array_merge($this->payload('critical'), [
            'alerts_sent' => ['email' => true, 'telegram' => true],
        ]))->assertOk();

        $row = N8nHeartbeat::first();
        $this->assertTrue($row->alerts_sent['email']);
        $this->assertTrue($row->alerts_sent['telegram']);
    }
}
