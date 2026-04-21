<?php

declare(strict_types=1);

return [

    'credits' => [
        'unlock_cost' => (int) env('ZAWAJ_UNLOCK_COST', 1),
    ],

    'matching' => [
        'round_timeout_minutes' => (int) env('ZAWAJ_ROUND_TIMEOUT_MINUTES', 120),
        'providers_per_round'   => (int) env('ZAWAJ_PROVIDERS_PER_ROUND', 3),
        'max_rounds'            => 3,
    ],

    'anpdp' => [
        'retention_days' => (int) env('ZAWAJ_RETENTION_DAYS', 14),
    ],

    'whatsapp' => [
        'link_prefix' => 'https://wa.me/',
    ],
];
