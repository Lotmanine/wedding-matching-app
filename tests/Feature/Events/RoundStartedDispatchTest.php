<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\RoundStarted;
use App\Jobs\DeliverWebhookJob;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WedRequest;
use App\Support\Whatsapp\SignedButtonPayload;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class RoundStartedDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);

        // Subscribe an active webhook to round.started so WebhookDispatcher queues a job
        WebhookEndpoint::create([
            'name'   => 'test-n8n',
            'url'    => 'https://n8n.test/webhook/zawaj-whatsapp-outbound',
            'secret' => 'test-secret',
            'events' => ['round.started', 'lead.accepted', 'lead.opened_to_marketplace'],
            'active' => true,
        ]);
    }

    private function makeProvider(): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'phone'    => '+213555111222',
            'whatsapp' => '+213555111222',
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['ar', 'fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);
        ProviderService::create(['user_id' => $u->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $u->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $u->fresh(['providerProfile']);
    }

    private function makeServiceRequest(): ServiceRequest
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Fatima',
            'couple_whatsapp'    => '+213555000111',
            'couple_wilaya_code' => '16',
            'couple_commune'     => 'Bab Ezzouar',
            'wedding_date'       => '2026-07-15',
            'services_needed'    => ['photo'],
        ]);

        return ServiceRequest::create([
            'wed_request_id'   => $wr->id,
            'service_type'     => 'photo',
            'status'           => 'round1',
            'current_round'    => 1,
            'round_started_at' => now(),
        ]);
    }

    public function test_payload_shape_and_signed_button_roundtrip(): void
    {
        $sr = $this->makeServiceRequest();
        $p1 = $this->makeProvider();
        $p2 = $this->makeProvider();

        Bus::fake([DeliverWebhookJob::class]);

        RoundStarted::dispatch($sr, 1, [$p1->id, $p2->id]);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($sr, $p1) {
            if ($job->eventType !== 'round.started') {
                return false;
            }

            $envelope = $job->payload;
            $data = $envelope['data'];

            // Top-level shape
            if ($data['service_request_id'] !== $sr->service_uid) return false;
            if ($data['round_number'] !== 1) return false;
            if ($data['template_intent'] !== 'round_teaser') return false;
            if ($data['couple']['first_name'] !== 'Fatima') return false;
            if ($data['service']['type'] !== 'photo') return false;
            if (count($data['recipients']) !== 2) return false;

            $r1 = $data['recipients'][0];
            if (count($r1['buttons']) !== 2) return false;

            // Decode the first button's signed token; must round-trip to (this SR, this provider, accept)
            $decoded = SignedButtonPayload::decode($r1['buttons'][0]['id']);

            return $decoded['service_request_uid'] === $sr->service_uid
                && in_array($decoded['provider_id'], [$p1->id, $r1['provider_id']], true)
                && $decoded['intent'] === 'accept';
        });
    }

    public function test_excludes_unverified_providers(): void
    {
        $sr = $this->makeServiceRequest();
        $verified = $this->makeProvider();
        $unverified = $this->makeProvider();
        $unverified->providerProfile->forceFill(['verified' => false])->save();

        Bus::fake([DeliverWebhookJob::class]);
        RoundStarted::dispatch($sr, 1, [$verified->id, $unverified->id]);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($verified, $unverified) {
            $ids = collect($job->payload['data']['recipients'])->pluck('provider_id')->all();

            return in_array($verified->id, $ids, true) && ! in_array($unverified->id, $ids, true);
        });
    }
}
