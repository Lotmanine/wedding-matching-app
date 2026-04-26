<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\LeadAccepted;
use App\Jobs\DeliverWebhookJob;
use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class LeadAcceptedDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);

        WebhookEndpoint::create([
            'name'   => 'test-n8n',
            'url'    => 'https://n8n.test/webhook/zawaj-whatsapp-outbound',
            'secret' => 'test-secret',
            'events' => ['lead.accepted'],
            'active' => true,
        ]);
    }

    public function test_payload_includes_winner_and_couple_blocks(): void
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Yasmine',
            'couple_whatsapp'    => '+213555000222',
            'couple_wilaya_code' => '16',
            'wedding_date'       => '2026-08-20',
            'services_needed'    => ['photo'],
        ]);
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => 'accepted',
            'current_round'  => 1,
        ]);

        $winner = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'name'     => 'Karim',
            'phone'    => '+213555111333',
            'whatsapp' => '+213555111333',
        ]);
        $winner->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $winner->id,
            'business_name'       => 'Studio Karim',
            'primary_wilaya_code' => '16',
            'verified'            => true,
            'languages'           => ['ar'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);

        Bus::fake([DeliverWebhookJob::class]);
        LeadAccepted::dispatch($sr, $winner);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($sr) {
            if ($job->eventType !== 'lead.accepted') return false;
            $data = $job->payload['data'];

            return $data['service_request_id'] === $sr->service_uid
                && $data['winner']['business_name']      === 'Studio Karim'
                && $data['winner']['preferred_language'] === 'ar'
                && $data['couple']['first_name']         === 'Yasmine'
                && $data['couple']['phone_e164']         === '+213555000222'
                && $data['template_intent']              === 'winner_confirmation';
        });
    }
}
