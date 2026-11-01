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
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class LeadAcceptedDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);

        WebhookEndpoint::create([
            'name'   => 'test-n8n',
            'url'    => 'https://n8n.test/webhook/wedding-whatsapp-outbound',
            'secret' => 'test-secret',
            'events' => ['lead.accepted'],
            'active' => true,
        ]);
    }

    public function test_payload_includes_winner_and_couple_blocks(): void
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Yasmine',
            'couple_whatsapp'    => '+12125550222',
            'couple_city_code' => 'NY',
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
            'phone'    => '+12125551333',
            'whatsapp' => '+12125551333',
        ]);
        $winner->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $winner->id,
            'business_name'       => 'Studio Karim',
            'primary_city_code' => 'NY',
            'verified'            => true,
            'languages'           => ['en'],
            'business_type'       => 'sole_proprietor',
            'price_tier'          => 'mid',
        ]);

        Bus::fake([DeliverWebhookJob::class]);
        LeadAccepted::dispatch($sr, $winner);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($sr) {
            if ($job->eventType !== 'lead.accepted') return false;
            $data = $job->payload['data'];

            return $data['service_request_id'] === $sr->service_uid
                && $data['winner']['business_name']      === 'Studio Karim'
                && $data['winner']['preferred_language'] === 'en'
                && $data['couple']['first_name']         === 'Yasmine'
                && $data['couple']['phone_e164']         === '+12125550222'
                && $data['template_intent']              === 'winner_confirmation';
        });
    }
}
