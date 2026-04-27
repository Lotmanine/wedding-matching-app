<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Events\CreditsGranted;
use App\Jobs\DeliverWebhookJob;
use App\Models\CreditLedger;
use App\Models\ProviderProfile;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Database\Seeders\RolesSeeder;
use Database\Seeders\UnlockPackSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class PurchaseCompletedDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(UnlockPackSeeder::class);

        WebhookEndpoint::create([
            'name'   => 'test-n8n',
            'url'    => 'https://n8n.test/webhook/zawaj-ops',
            'secret' => 'test-secret',
            'events' => ['purchase.completed'],
            'active' => true,
        ]);
    }

    private function makePurchase(?\Carbon\CarbonInterface $emailedAt = null): array
    {
        $user = User::factory()->create([
            'role'   => 'provider',
            'email'  => 'amine@studio.dz',
            'name'   => 'Amine',
        ]);
        $user->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'business_name'       => 'Studio Amine',
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['ar', 'fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);

        $pack = UnlockPack::first();
        $purchase = Purchase::create([
            'user_id'            => $user->id,
            'unlock_pack_id'     => $pack->id,
            'amount_dzd'         => $pack->price_dzd,
            'unlocks_count'      => $pack->unlocks_count,
            'status'             => 'paid',
            'gateway'            => 'chargily',
            'paid_at'            => now(),
            'credits_granted_at' => now(),
            'receipt_emailed_at' => $emailedAt,
        ]);

        $ledger = CreditLedger::create([
            'user_id'       => $user->id,
            'delta'         => $pack->unlocks_count,
            'balance_after' => $pack->unlocks_count,
            'type'          => 'purchase',
        ]);

        return [$user->fresh(['providerProfile']), $purchase->fresh(['user.providerProfile', 'unlockPack']), $ledger];
    }

    public function test_payload_contains_enriched_fields(): void
    {
        Bus::fake([DeliverWebhookJob::class]);

        [$user, $purchase, $ledger] = $this->makePurchase();

        CreditsGranted::dispatch($purchase, $ledger, $user);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($purchase, $user) {
            if ($job->eventType !== 'purchase.completed') return false;
            $data = $job->payload['data'];

            return $data['purchase_uid']        === $purchase->purchase_uid
                && $data['provider_id']         === $user->id
                && $data['provider_email']      === 'amine@studio.dz'
                && $data['provider_name']       === 'Studio Amine'
                && in_array($data['preferred_language'], ['ar', 'fr'], true)
                && ! empty($data['pack_name'])
                && $data['amount_dzd']          === (int) $purchase->amount_dzd
                && $data['credits_granted']     === (int) $purchase->unlocks_count
                && str_ends_with($data['receipt_pdf_url'], "/api/v1/n8n/receipts/{$purchase->purchase_uid}.pdf");
        });
    }

    public function test_idempotency_skips_when_already_emailed(): void
    {
        Bus::fake([DeliverWebhookJob::class]);

        [$user, $purchase, $ledger] = $this->makePurchase(emailedAt: now()->subMinute());

        CreditsGranted::dispatch($purchase, $ledger, $user);

        Bus::assertNotDispatched(DeliverWebhookJob::class);
    }
}
