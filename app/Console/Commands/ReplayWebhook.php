<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class ReplayWebhook extends Command
{
    protected $signature = 'zawaj:replay-webhook {deliveryId}';

    protected $description = 'Re-queue a specific webhook delivery with its original payload.';

    public function handle(): int
    {
        $id = (int) $this->argument('deliveryId');
        $src = WebhookDelivery::find($id);

        if (! $src) {
            $this->error("Delivery {$id} not found.");

            return self::FAILURE;
        }

        DeliverWebhookJob::dispatch($src->webhook_endpoint_id, $src->event_type, (array) $src->payload);

        $this->info("Re-queued delivery based on #{$src->id} → endpoint {$src->webhook_endpoint_id}, event {$src->event_type}.");

        return self::SUCCESS;
    }
}
