<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Events\RoundStarted;
use App\Events\WedRequestSubmitted;
use App\Jobs\DeliverWebhookJob;
use App\Listeners\Whatsapp\DispatchRoundStartedWebhook;
use App\Models\ServiceRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WedRequest;
use App\Services\Webhooks\WebhookDispatcher;
use Database\Seeders\CitySeeder;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebhookDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatcher_queues_job_for_subscribed_endpoint(): void
    {
        Bus::fake();

        $endpoint = WebhookEndpoint::create([
            'name'   => 'n8n',
            'url'    => 'https://n8n.example.com/webhook/x',
            'secret' => 'sekret',
            'events' => ['service_request.accepted'],
            'active' => true,
        ]);
        WebhookEndpoint::create([
            'name'   => 'other',
            'url'    => 'https://other.example.com/hook',
            'secret' => 'sekret',
            'events' => ['provider.registered'],
            'active' => true,
        ]);

        app(WebhookDispatcher::class)->dispatch('service_request.accepted', ['service_uid' => 'SRV-X']);

        Bus::assertDispatched(DeliverWebhookJob::class, fn ($job) =>
            $job->endpointId === $endpoint->id && $job->eventType === 'service_request.accepted'
        );
        Bus::assertDispatchedTimes(DeliverWebhookJob::class, 1);
    }

    public function test_domain_event_queues_exactly_one_delivery_per_endpoint(): void
    {
        Bus::fake([DeliverWebhookJob::class]);
        $this->seed(CitySeeder::class);

        WebhookEndpoint::create([
            'name'   => 'n8n Core',
            'url'    => 'https://n8n.example.com/webhook/wedding-core',
            'secret' => 'sekret',
            'events' => ['wed_request.submitted'],
            'active' => true,
        ]);

        WedRequestSubmitted::dispatch(WedRequest::factory()->create(['couple_city_code' => 'NY']));

        // Regression: listeners used to be registered twice (explicitly + by
        // auto-discovery), so every webhook went out in duplicate.
        Bus::assertDispatchedTimes(DeliverWebhookJob::class, 1);
    }

    public function test_round_started_queues_the_enriched_whatsapp_listener_once(): void
    {
        Queue::fake();
        $this->seed(CitySeeder::class);

        $wr = WedRequest::factory()->create(['couple_city_code' => 'NY']);
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => 'round1',
            'current_round'  => 1,
        ]);

        RoundStarted::dispatch($sr, 1, [1]);

        $queued = Queue::pushed(
            CallQueuedListener::class,
            fn ($job) => $job->class === DispatchRoundStartedWebhook::class,
        );
        $this->assertCount(1, $queued);
    }

    public function test_successful_delivery_signs_body_and_marks_delivered(): void
    {
        Http::fake(['*' => Http::response('{"ok":true}', 200)]);

        $endpoint = WebhookEndpoint::create([
            'name'   => 'n8n',
            'url'    => 'https://n8n.example.com/webhook/x',
            'secret' => 'sekret',
            'events' => ['service_request.accepted'],
            'active' => true,
        ]);

        $payload = ['event' => 'service_request.accepted', 'data' => ['foo' => 'bar']];

        (new DeliverWebhookJob($endpoint->id, 'service_request.accepted', $payload))->handle();

        $delivery = WebhookDelivery::first();
        $this->assertNotNull($delivery);
        $this->assertNotNull($delivery->delivered_at);
        $this->assertSame(200, $delivery->response_status);

        $expectedSig = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE), 'sekret');
        Http::assertSent(function ($request) use ($expectedSig) {
            return $request->header('X-Wedding-Signature')[0] === $expectedSig
                && $request->header('X-Wedding-Event')[0] === 'service_request.accepted';
        });
    }

    public function test_failing_delivery_marks_failed_and_sets_retry(): void
    {
        Http::fake(['*' => Http::response('err', 500)]);

        $endpoint = WebhookEndpoint::create([
            'name'   => 'n8n',
            'url'    => 'https://n8n.example.com/webhook/x',
            'secret' => 'sekret',
            'events' => ['provider.registered'],
            'active' => true,
        ]);

        $job = new DeliverWebhookJob($endpoint->id, 'provider.registered', ['event' => 'provider.registered']);
        $job->handle();

        $d = WebhookDelivery::first();
        $this->assertNotNull($d->failed_at);
        $this->assertSame(500, $d->response_status);
        $this->assertSame(1, $d->attempts);
        $this->assertNotNull($d->next_retry_at);
    }

    public function test_inactive_endpoint_not_delivered_to(): void
    {
        Http::fake();

        $endpoint = WebhookEndpoint::create([
            'name'   => 'off',
            'url'    => 'https://off.example.com',
            'secret' => 'x',
            'events' => ['provider.registered'],
            'active' => false,
        ]);

        (new DeliverWebhookJob($endpoint->id, 'provider.registered', ['event' => 'provider.registered']))->handle();

        Http::assertNothingSent();
        $this->assertSame(0, WebhookDelivery::count());
    }
}
