<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\N8n;

use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\UnlockPackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReceiptStreamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(UnlockPackSeeder::class);
    }

    private function asN8n(): void
    {
        $u = User::factory()->create(['role' => 'admin']);
        $u->assignRole('admin');
        Sanctum::actingAs($u, ['n8n-service']);
    }

    private function makePaidPurchase(): Purchase
    {
        $user = User::factory()->create(['role' => 'provider']);
        $user->assignRole('provider');
        $pack = UnlockPack::first();

        return Purchase::create([
            'user_id'        => $user->id,
            'unlock_pack_id' => $pack->id,
            'amount_dzd'     => $pack->price_dzd,
            'unlocks_count'  => $pack->unlocks_count,
            'status'         => 'paid',
            'gateway'        => 'chargily',
            'paid_at'        => now(),
        ]);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $this->get('/api/v1/n8n/receipts/PUR-X.pdf', ['Accept' => 'application/json'])
            ->assertStatus(401);
    }

    public function test_unknown_purchase_returns_404(): void
    {
        $this->asN8n();

        $this->get('/api/v1/n8n/receipts/PUR-DOES-NOT-EXIST.pdf')->assertStatus(404);
    }

    public function test_valid_purchase_returns_pdf(): void
    {
        $this->asN8n();
        $purchase = $this->makePaidPurchase();

        $resp = $this->get('/api/v1/n8n/receipts/' . $purchase->purchase_uid . '.pdf');
        $resp->assertOk();
        $this->assertSame('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertStringContainsString('receipt-' . $purchase->purchase_uid . '.pdf', $resp->headers->get('Content-Disposition'));
    }

    public function test_mark_emailed_persists_fields(): void
    {
        $this->asN8n();
        $purchase = $this->makePaidPurchase();

        $this->patchJson('/api/v1/n8n/receipts/' . $purchase->purchase_uid . '/mark-emailed', [
            'sent_at'         => '2026-04-27T10:00:00+01:00',
            'smtp_message_id' => '<smtp-msg-id-12345@zawajpro.dz>',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'marked');

        $purchase->refresh();
        $this->assertNotNull($purchase->receipt_emailed_at);
        $this->assertSame('<smtp-msg-id-12345@zawajpro.dz>', $purchase->smtp_message_id);
    }

    public function test_mark_emailed_idempotent(): void
    {
        $this->asN8n();
        $purchase = $this->makePaidPurchase();

        $this->patchJson('/api/v1/n8n/receipts/' . $purchase->purchase_uid . '/mark-emailed', [
            'sent_at'         => '2026-04-27T10:00:00+01:00',
            'smtp_message_id' => '<msg-1>',
        ])->assertOk();

        // Second call returns already_marked
        $this->patchJson('/api/v1/n8n/receipts/' . $purchase->purchase_uid . '/mark-emailed', [
            'sent_at'         => '2026-04-27T10:05:00+01:00',
            'smtp_message_id' => '<msg-2>',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'already_marked');

        // First message ID preserved
        $this->assertSame('<msg-1>', $purchase->fresh()->smtp_message_id);
    }
}
