<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Events\LeadOpenedToMarketplace;
use App\Jobs\DeliverWebhookJob;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class LeadOpenedToMarketplaceDispatchTest extends TestCase
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
            'events' => ['lead.opened_to_marketplace'],
            'active' => true,
        ]);
    }

    private function makeProvider(bool $verified = true): User
    {
        $u = User::factory()->create([
            'role'     => 'provider',
            'status'   => 'active',
            'whatsapp' => '+213' . str_pad((string) random_int(500000000, 799999999), 9, '0'),
        ]);
        $u->assignRole('provider');
        ProviderProfile::factory()->create([
            'user_id'             => $u->id,
            'primary_wilaya_code' => '16',
            'verified'            => $verified,
            'languages'           => ['ar'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
        ]);
        ProviderService::create(['user_id' => $u->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $u->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $u->fresh('providerProfile');
    }

    public function test_recipients_include_eligible_and_exclude_unverified(): void
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Sara',
            'couple_wilaya_code' => '16',
            'services_needed'    => ['photo'],
        ]);
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => 'open_marketplace',
            'current_round'  => 4,
        ]);

        $verified = $this->makeProvider(verified: true);
        $unverified = $this->makeProvider(verified: false);

        Bus::fake([DeliverWebhookJob::class]);
        LeadOpenedToMarketplace::dispatch($sr);

        Bus::assertDispatched(DeliverWebhookJob::class, function (DeliverWebhookJob $job) use ($verified, $unverified) {
            if ($job->eventType !== 'lead.opened_to_marketplace') return false;
            $ids = collect($job->payload['data']['recipients'])->pluck('provider_id')->all();

            return in_array($verified->id, $ids, true) && ! in_array($unverified->id, $ids, true);
        });
    }
}
