<?php

declare(strict_types=1);

namespace App\Support\Ops;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class HealthSnapshot
{
    /**
     * @param  Collection<int,array<string,mixed>>  $stuckMatching
     * @param  Collection<int,array<string,mixed>>  $failedWebhooks24h
     * @param  Collection<int,array<string,mixed>>  $failedWhatsapp24h
     * @param  Collection<int,array<string,mixed>>  $n8nErrors24h
     */
    public function __construct(
        public readonly string $severity,
        public readonly Collection $stuckMatching,
        public readonly Collection $failedWebhooks24h,
        public readonly Collection $failedWhatsapp24h,
        public readonly Collection $n8nErrors24h,
        public readonly CarbonInterface $runAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'severity'             => $this->severity,
            'run_at'               => $this->runAt->toAtomString(),
            'stuck_matching'       => $this->stuckMatching->all(),
            'failed_webhooks_24h'  => $this->failedWebhooks24h->all(),
            'failed_whatsapp_24h'  => $this->failedWhatsapp24h->all(),
            'n8n_errors_24h'       => $this->n8nErrors24h->all(),
        ];
    }
}
