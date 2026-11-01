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

    private const SECRET = 'n8n-shared-secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        config(['wedding.n8n.webhook_secret' => self::SECRET]);
    }

    /** Post to the error webhook with the valid shared-secret header. */
    private function postError(array $payload)
    {
        return $this->withHeaders(['X-Wedding-Token' => self::SECRET])
            ->postJson('/api/webhooks/n8n/error', $payload);
    }

    public function test_error_payload_logged_and_audited(): void
    {
        $payload = [
            'workflow' => 'wedding-matching-orchestrator',
            'execution_id' => 'exec_abc123',
            'failed_node' => 'Claude AI Rank',
            'error_message' => 'Invalid JSON',
            'timestamp' => now()->toAtomString(),
        ];

        $resp = $this->postError($payload);
        $resp->assertOk()->assertJsonPath('received', true);

        $this->assertTrue(AuditLog::where('action', 'n8n.error')->exists());
        $log = AuditLog::where('action', 'n8n.error')->first();
        $this->assertSame('wedding-matching-orchestrator', $log->metadata['workflow']);
        $this->assertSame('Claude AI Rank', $log->metadata['failed_node']);
    }

    public function test_request_without_valid_token_is_rejected(): void
    {
        // No header at all.
        $this->postJson('/api/webhooks/n8n/error', ['workflow' => 'x'])
            ->assertStatus(401);

        // Wrong token.
        $this->withHeaders(['X-Wedding-Token' => 'wrong'])
            ->postJson('/api/webhooks/n8n/error', ['workflow' => 'x'])
            ->assertStatus(401);

        $this->assertFalse(AuditLog::where('action', 'n8n.error')->exists());
    }

    public function test_alert_threshold_fires_after_five_errors(): void
    {
        $payload = ['workflow' => 'wedding-matching-orchestrator', 'error_message' => 'x'];

        for ($i = 0; $i < 4; $i++) {
            $this->postError($payload)->assertJsonPath('alert_threshold', false);
        }

        $this->postError($payload)->assertJsonPath('alert_threshold', true);
    }

    public function test_rate_limit_after_60_requests_per_minute(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->postError(['workflow' => 'x']);
        }

        $this->postError(['workflow' => 'x'])->assertStatus(429);
    }
}
