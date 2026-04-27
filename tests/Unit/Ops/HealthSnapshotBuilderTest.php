<?php

declare(strict_types=1);

namespace Tests\Unit\Ops;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WhatsappMessage;
use App\Models\WedRequest;
use App\Support\Ops\HealthSnapshot;
use App\Support\Ops\HealthSnapshotBuilder;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthSnapshotBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeStuckSr(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $wr = WedRequest::factory()->create(['services_needed' => ['photo']]);
            ServiceRequest::create([
                'wed_request_id'   => $wr->id,
                'service_type'     => 'photo',
                'status'           => 'matching',
                'current_round'    => 1,
                'round_started_at' => now()->subHours(5),
            ]);
        }
    }

    private function makeFailedWebhooks(int $count): void
    {
        $endpoint = WebhookEndpoint::create([
            'name' => 'test', 'url' => 'https://x', 'secret' => 's', 'events' => ['x'], 'active' => true,
        ]);
        for ($i = 0; $i < $count; $i++) {
            WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event_type'          => 'round.started',
                'payload'             => ['x' => 1],
                'response_status'     => 500,
                'attempts'            => 6,
                'failed_at'           => now()->subHours(2),
            ]);
        }
    }

    private function makeFailedWhatsapp(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            WhatsappMessage::factory()->create([
                'status'     => 'failed',
                'error'      => '131026: receiver incapable',
                'updated_at' => now()->subHours(2),
            ]);
        }
    }

    private function makeN8nErrors(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            AuditLog::create([
                'action'     => 'n8n.error',
                'metadata'   => ['workflow' => 'zawaj-matching', 'failed_node' => 'Claude Rank'],
                'created_at' => now()->subHours(1),
            ]);
        }
    }

    public function test_empty_system_returns_none(): void
    {
        $snap = (new HealthSnapshotBuilder())->build();

        $this->assertInstanceOf(HealthSnapshot::class, $snap);
        $this->assertSame(HealthSnapshotBuilder::SEVERITY_NONE, $snap->severity);
        $this->assertSame(0, $snap->stuckMatching->count());
    }

    public function test_one_stuck_sr_triggers_warn(): void
    {
        $this->makeStuckSr(1);

        $this->assertSame(HealthSnapshotBuilder::SEVERITY_WARN, (new HealthSnapshotBuilder())->build()->severity);
    }

    public function test_ten_stuck_sr_triggers_critical(): void
    {
        $this->makeStuckSr(10);

        $this->assertSame(HealthSnapshotBuilder::SEVERITY_CRITICAL, (new HealthSnapshotBuilder())->build()->severity);
    }

    public function test_failed_webhooks_threshold(): void
    {
        $this->makeFailedWebhooks(5);
        $this->assertSame(HealthSnapshotBuilder::SEVERITY_WARN, (new HealthSnapshotBuilder())->build()->severity);

        $this->makeFailedWebhooks(15); // total 20
        $this->assertSame(HealthSnapshotBuilder::SEVERITY_CRITICAL, (new HealthSnapshotBuilder())->build()->severity);
    }

    public function test_n8n_errors_threshold(): void
    {
        $this->makeN8nErrors(1);
        $this->assertSame(HealthSnapshotBuilder::SEVERITY_WARN, (new HealthSnapshotBuilder())->build()->severity);

        $this->makeN8nErrors(4); // total 5
        $this->assertSame(HealthSnapshotBuilder::SEVERITY_CRITICAL, (new HealthSnapshotBuilder())->build()->severity);
    }

    public function test_to_array_shape(): void
    {
        $this->makeStuckSr(2);
        $arr = (new HealthSnapshotBuilder())->build()->toArray();

        $this->assertSame('warn', $arr['severity']);
        $this->assertNotEmpty($arr['run_at']);
        $this->assertCount(2, $arr['stuck_matching']);
        $this->assertArrayHasKey('service_uid', $arr['stuck_matching'][0]);
        $this->assertArrayHasKey('current_round', $arr['stuck_matching'][0]);
        $this->assertArrayHasKey('round_started_at', $arr['stuck_matching'][0]);
    }
}
