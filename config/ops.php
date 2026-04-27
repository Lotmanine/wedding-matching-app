<?php

declare(strict_types=1);

/**
 * Ops health-snapshot severity thresholds. Each tier triggers when ANY
 * configured threshold is reached. Lower bound for `warn`, upper bound
 * (inclusive) for `critical`.
 *
 * Rules: critical > warn. Both default to env-overridable so Lotfi can
 * tune thresholds without redeploying — useful when a noisy day produces
 * false-positive criticals.
 */

return [
    'severity' => [
        'warn' => [
            'stuck_matching'      => (int) env('OPS_WARN_STUCK_MATCHING', 1),
            'failed_webhooks_24h' => (int) env('OPS_WARN_FAILED_WEBHOOKS', 5),
            'failed_whatsapp_24h' => (int) env('OPS_WARN_FAILED_WHATSAPP', 10),
            'n8n_errors_24h'      => (int) env('OPS_WARN_N8N_ERRORS', 1),
        ],
        'critical' => [
            'stuck_matching'      => (int) env('OPS_CRIT_STUCK_MATCHING', 10),
            'failed_webhooks_24h' => (int) env('OPS_CRIT_FAILED_WEBHOOKS', 20),
            'failed_whatsapp_24h' => (int) env('OPS_CRIT_FAILED_WHATSAPP', 50),
            'n8n_errors_24h'      => (int) env('OPS_CRIT_N8N_ERRORS', 5),
        ],
    ],

    /** Hours since round_started_at that count as "stuck". */
    'stuck_matching_hours' => (int) env('OPS_STUCK_MATCHING_HOURS', 4),
];
