<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class InspectWebhookDeliveries extends Command
{
    protected $signature = 'zawaj:webhook-deliveries {--failed} {--event=} {--limit=20}';

    protected $description = 'Inspect recent webhook deliveries (optionally failed-only or filtered by event_type)';

    public function handle(): int
    {
        $query = WebhookDelivery::with('endpoint')->latest('created_at');

        if ($this->option('failed')) {
            $query->whereNotNull('failed_at')->whereNull('delivered_at');
        }
        if ($ev = $this->option('event')) {
            $query->where('event_type', $ev);
        }

        $rows = $query->limit((int) $this->option('limit'))->get();

        if ($rows->isEmpty()) {
            $this->info('No deliveries match.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Event', 'Endpoint', 'Status', 'Attempts', 'Created', 'Delivered'],
            $rows->map(fn ($d) => [
                $d->id,
                $d->event_type,
                $d->endpoint?->name ?? '—',
                $d->response_status ?? ($d->delivered_at ? 'ok' : ($d->failed_at ? 'failed' : 'pending')),
                $d->attempts,
                $d->created_at?->diffForHumans(),
                $d->delivered_at?->diffForHumans() ?? '—',
            ])->all()
        );

        return self::SUCCESS;
    }
}
