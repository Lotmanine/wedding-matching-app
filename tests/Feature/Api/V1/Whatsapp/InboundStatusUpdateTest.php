<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Whatsapp;

use App\Models\User;
use App\Models\WhatsappMessage;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InboundStatusUpdateTest extends TestCase
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

    private function seedOutboundRow(string $waMessageId, string $status = 'sent'): WhatsappMessage
    {
        return WhatsappMessage::factory()->create([
            'wa_message_id' => $waMessageId,
            'status'        => $status,
            'direction'     => 'outbound',
        ]);
    }

    private function payload(string $waMessageId, string $status, ?string $error = null): array
    {
        return [
            'wa_message_id' => $waMessageId,
            'status'        => $status,
            'recipient_id'  => '+213555111222',
            'timestamp'     => '1745596800',
            'error'         => $error,
        ];
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->postJson('/api/v1/whatsapp/inbound/status-update', [])->assertStatus(401);
    }

    public function test_valid_update_advances_status(): void
    {
        $this->asN8n();
        $row = $this->seedOutboundRow('wamid.A', status: 'sent');

        $this->postJson('/api/v1/whatsapp/inbound/status-update', $this->payload('wamid.A', 'delivered'))
            ->assertOk()
            ->assertJsonPath('data.status', 'updated')
            ->assertJsonPath('data.previous', 'sent')
            ->assertJsonPath('data.current', 'delivered');

        $this->assertSame('delivered', $row->fresh()->status);
    }

    public function test_regression_blocked(): void
    {
        $this->asN8n();
        $row = $this->seedOutboundRow('wamid.B', status: 'read');

        $this->postJson('/api/v1/whatsapp/inbound/status-update', $this->payload('wamid.B', 'delivered'))
            ->assertOk()
            ->assertJsonPath('data.status', 'regression_blocked');

        $this->assertSame('read', $row->fresh()->status);
    }

    public function test_failed_always_wins(): void
    {
        $this->asN8n();
        $row = $this->seedOutboundRow('wamid.C', status: 'read');

        $this->postJson('/api/v1/whatsapp/inbound/status-update', $this->payload('wamid.C', 'failed', error: '131026: receiver incapable'))
            ->assertOk()
            ->assertJsonPath('data.status', 'updated');

        $fresh = $row->fresh();
        $this->assertSame('failed', $fresh->status);
        $this->assertStringContainsString('131026', $fresh->error);
    }

    public function test_unknown_wamid_returns_unknown_no_row_created(): void
    {
        $this->asN8n();

        $this->postJson('/api/v1/whatsapp/inbound/status-update', $this->payload('wamid.NEVER_SEEN', 'delivered'))
            ->assertOk()
            ->assertJsonPath('data.status', 'unknown_wamid');

        $this->assertSame(0, WhatsappMessage::count());
    }

    public function test_repeated_identical_status_is_no_op(): void
    {
        $this->asN8n();
        $row = $this->seedOutboundRow('wamid.D', status: 'delivered');

        $this->postJson('/api/v1/whatsapp/inbound/status-update', $this->payload('wamid.D', 'delivered'))
            ->assertOk()
            ->assertJsonPath('data.status', 'no_change');

        $this->assertSame('delivered', $row->fresh()->status);
    }
}
