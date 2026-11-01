<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Webhooks;

use App\Models\User;
use App\Models\WhatsappMessage;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WhatsappDeliveryStatusTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'event'              => 'round.started',
            'service_request_id' => 'SRV-20260422-T001',
            'results'            => [[
                'role'                 => 'provider',
                'recipient_phone_e164' => '+12125551222',
                'template_intent'      => 'round_teaser',
                'provider_id'          => 12,
                'wa_message_id'        => 'wamid.HBgL12345',
                'status'               => 'sent',
                'error'                => null,
            ]],
        ], $overrides);
    }

    public function test_requires_auth(): void
    {
        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $this->payload())
            ->assertStatus(401);
    }

    public function test_persists_a_row_per_result(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $this->payload())
            ->assertOk()
            ->assertJsonPath('data.recorded', 1)
            ->assertJsonPath('data.duplicate', 0);

        $this->assertSame(1, WhatsappMessage::count());

        $row = WhatsappMessage::first();
        $this->assertSame('SRV-20260422-T001', $row->service_request_uid);
        $this->assertSame('wamid.HBgL12345', $row->wa_message_id);
        $this->assertSame('sent', $row->status);
    }

    public function test_idempotent_repeated_wamid_does_not_duplicate(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $this->payload())->assertOk();
        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $this->payload())
            ->assertOk()
            ->assertJsonPath('data.recorded', 0)
            ->assertJsonPath('data.duplicate', 1);

        $this->assertSame(1, WhatsappMessage::count());
    }

    public function test_failed_result_persists_with_error(): void
    {
        $this->asN8n();

        $payload = $this->payload([
            'results' => [[
                'role'                 => 'provider',
                'recipient_phone_e164' => '+12125551222',
                'template_intent'      => 'round_teaser',
                'provider_id'          => 12,
                'wa_message_id'        => null,
                'status'               => 'failed',
                'error'                => '131026: Receiver incapable',
            ]],
        ]);

        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $payload)->assertOk();

        $row = WhatsappMessage::first();
        $this->assertSame('failed', $row->status);
        $this->assertStringContainsString('131026', $row->error);
        $this->assertNull($row->wa_message_id);
    }

    public function test_validation_rejects_unknown_status(): void
    {
        $this->asN8n();

        $payload = $this->payload([
            'results' => [[
                'role'                 => 'provider',
                'recipient_phone_e164' => '+1212555...',
                'status'               => 'exploded',
            ]],
        ]);

        $this->postJson('/api/v1/webhooks/whatsapp/delivery-status', $payload)
            ->assertStatus(422);
    }
}
