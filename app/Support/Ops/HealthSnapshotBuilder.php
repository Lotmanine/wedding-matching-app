<?php

declare(strict_types=1);

namespace App\Support\Ops;

use App\Models\AuditLog;
use App\Models\ServiceRequest;
use App\Models\WebhookDelivery;
use App\Models\WhatsappMessage;
use Illuminate\Support\Collection;

class HealthSnapshotBuilder
{
    public const SEVERITY_NONE     = 'none';
    public const SEVERITY_WARN     = 'warn';
    public const SEVERITY_CRITICAL = 'critical';

    public function build(): HealthSnapshot
    {
        $stuckHours = (int) config('ops.stuck_matching_hours', 4);

        $stuckMatching = ServiceRequest::query()
            ->where('status', 'matching')
            ->where('round_started_at', '<=', now()->subHours($stuckHours))
            ->orderByDesc('round_started_at')
            ->limit(50)
            ->get(['service_uid', 'current_round', 'round_started_at'])
            ->map(fn ($sr) => [
                'service_uid'      => $sr->service_uid,
                'current_round'    => (int) $sr->current_round,
                'round_started_at' => $sr->round_started_at?->toAtomString(),
            ]);

        $failedWebhooks = WebhookDelivery::query()
            ->whereNotNull('failed_at')
            ->whereNull('delivered_at')
            ->where('failed_at', '>=', now()->subDay())
            ->orderByDesc('failed_at')
            ->limit(100)
            ->get(['id', 'webhook_endpoint_id', 'event_type', 'response_status', 'attempts'])
            ->map(fn ($w) => [
                'id'              => (int) $w->id,
                'endpoint_id'     => (int) $w->webhook_endpoint_id,
                'event_type'      => $w->event_type,
                'response_status' => $w->response_status,
                'attempts'        => (int) $w->attempts,
            ]);

        $failedWhatsapp = WhatsappMessage::query()
            ->where('status', 'failed')
            ->where('updated_at', '>=', now()->subDay())
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get(['id', 'service_request_uid', 'event_type', 'error'])
            ->map(fn ($m) => [
                'id'                  => (int) $m->id,
                'service_request_uid' => $m->service_request_uid,
                'event_type'          => $m->event_type,
                'error'               => $m->error,
            ]);

        $n8nErrors = AuditLog::query()
            ->where('action', 'n8n.error')
            ->where('created_at', '>=', now()->subDay())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'metadata', 'created_at'])
            ->map(fn ($a) => [
                'id'         => (int) $a->id,
                'metadata'   => $a->metadata,
                'created_at' => $a->created_at?->toAtomString(),
            ]);

        $severity = $this->assessSeverity(
            stuckMatching: $stuckMatching->count(),
            failedWebhooks: $failedWebhooks->count(),
            failedWhatsapp: $failedWhatsapp->count(),
            n8nErrors: $n8nErrors->count(),
        );

        return new HealthSnapshot(
            severity: $severity,
            stuckMatching: $stuckMatching,
            failedWebhooks24h: $failedWebhooks,
            failedWhatsapp24h: $failedWhatsapp,
            n8nErrors24h: $n8nErrors,
            runAt: now(),
        );
    }

    private function assessSeverity(int $stuckMatching, int $failedWebhooks, int $failedWhatsapp, int $n8nErrors): string
    {
        $crit = (array) config('ops.severity.critical', []);
        if (
            $stuckMatching >= ($crit['stuck_matching']      ?? PHP_INT_MAX)
            || $failedWebhooks >= ($crit['failed_webhooks_24h'] ?? PHP_INT_MAX)
            || $failedWhatsapp >= ($crit['failed_whatsapp_24h'] ?? PHP_INT_MAX)
            || $n8nErrors      >= ($crit['n8n_errors_24h']      ?? PHP_INT_MAX)
        ) {
            return self::SEVERITY_CRITICAL;
        }

        $warn = (array) config('ops.severity.warn', []);
        if (
            $stuckMatching >= ($warn['stuck_matching']      ?? PHP_INT_MAX)
            || $failedWebhooks >= ($warn['failed_webhooks_24h'] ?? PHP_INT_MAX)
            || $failedWhatsapp >= ($warn['failed_whatsapp_24h'] ?? PHP_INT_MAX)
            || $n8nErrors      >= ($warn['n8n_errors_24h']      ?? PHP_INT_MAX)
        ) {
            return self::SEVERITY_WARN;
        }

        return self::SEVERITY_NONE;
    }
}
