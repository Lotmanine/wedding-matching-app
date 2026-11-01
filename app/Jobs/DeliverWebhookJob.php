<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeliverWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    public int $timeout = 30;

    public function __construct(
        public int $endpointId,
        public string $eventType,
        public array $payload,
        public ?int $existingDeliveryId = null,
    ) {
    }

    public function handle(): void
    {
        $endpoint = WebhookEndpoint::find($this->endpointId);
        if (! $endpoint || ! $endpoint->active) {
            return;
        }

        $body = json_encode($this->payload, JSON_UNESCAPED_UNICODE);
        $signature = hash_hmac('sha256', $body, $endpoint->secret);

        $delivery = $this->existingDeliveryId
            ? WebhookDelivery::find($this->existingDeliveryId)
            : WebhookDelivery::create([
                'webhook_endpoint_id' => $endpoint->id,
                'event_type'          => $this->eventType,
                'payload'             => $this->payload,
                'attempts'            => 0,
            ]);

        if (! $delivery) {
            return;
        }

        $delivery->increment('attempts');

        try {
            $response = Http::withHeaders([
                'X-Wedding-Event'        => $this->eventType,
                'X-Wedding-Signature'    => $signature,
                'X-Wedding-Delivery-Id'  => (string) $delivery->id,
                'Content-Type'         => 'application/json',
            ])
            ->timeout($this->timeout)
            ->withBody($body, 'application/json')
            ->post($endpoint->url);

            $delivery->forceFill([
                'response_status' => $response->status(),
                'response_body'   => substr((string) $response->body(), 0, 2000),
                'delivered_at'    => $response->successful() ? now() : null,
                'failed_at'       => $response->successful() ? null : now(),
                'next_retry_at'   => $response->successful() ? null : now()->addSeconds($this->backoffSeconds($delivery->attempts)),
            ])->save();

            if (! $response->successful() && $this->attempts() < $this->tries) {
                $this->release($this->backoffSeconds($delivery->attempts));
            }
        } catch (Throwable $e) {
            $delivery->forceFill([
                'response_body' => substr($e->getMessage(), 0, 2000),
                'failed_at'     => now(),
                'next_retry_at' => now()->addSeconds($this->backoffSeconds($delivery->attempts)),
            ])->save();

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoffSeconds($delivery->attempts));
            }
        }
    }

    private function backoffSeconds(int $attempt): int
    {
        return min(3600, 30 * (2 ** max(0, $attempt - 1)));
    }
}
