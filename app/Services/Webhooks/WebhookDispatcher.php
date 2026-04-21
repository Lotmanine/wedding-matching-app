<?php

declare(strict_types=1);

namespace App\Services\Webhooks;

use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookEndpoint;

class WebhookDispatcher
{
    public function dispatch(string $eventType, array $payload): void
    {
        $envelope = [
            'event'     => $eventType,
            'timestamp' => now()->toAtomString(),
            'data'      => $payload,
        ];

        WebhookEndpoint::where('active', true)->get()
            ->filter(fn ($e) => $e->subscribesTo($eventType))
            ->each(function (WebhookEndpoint $endpoint) use ($eventType, $envelope) {
                DeliverWebhookJob::dispatch($endpoint->id, $eventType, $envelope);
            });
    }
}
