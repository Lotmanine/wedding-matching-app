<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Models\AuditLog;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class N8nErrorLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_error_payload_logged_and_audited(): void
    {
        $payload = [
            'workflow'      => 'zawaj-matching-orchestrator',
            'execution_id'  => 'exec_abc123',
            'failed_node'   => 'Claude AI Rank',
            'error_message' => 'Invalid JSON',
            'timestamp'     => now()->toAtomString(),
        ];

        $resp = $this->postJson('/api/webhooks/n8n/error', $payload);
        $resp->assertOk()->assertJsonPath('received', true);

        $this->assertTrue(AuditLog::where('action', 'n8n.error')->exists());
        $log = AuditLog::where('action', 'n8n.error')->first();
        $this->assertSame('zawaj-matching-orchestrator', $log->metadata['workflow']);
        $this->assertSame('Claude AI Rank', $log->metadata['failed_node']);
    }

    public function test_alert_threshold_fires_after_five_errors(): void
    {
        $payload = ['workflow' => 'zawaj-matching-orchestrator', 'error_message' => 'x'];

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/webhooks/n8n/error', $payload)->assertJsonPath('alert_threshold', false);
        }

        $this->postJson('/api/webhooks/n8n/error', $payload)
            ->assertJsonPath('alert_threshold', true);
    }

    public function test_rate_limit_after_60_requests_per_minute(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->postJson('/api/webhooks/n8n/error', ['workflow' => 'x']);
        }

        $this->postJson('/api/webhooks/n8n/error', ['workflow' => 'x'])->assertStatus(429);
    }
}
