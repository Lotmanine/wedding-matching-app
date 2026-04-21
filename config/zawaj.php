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

    'chargily' => [
        'api_key'        => env('CHARGILY_API_KEY'),
        'secret_key'     => env('CHARGILY_SECRET_KEY'),
        'webhook_secret' => env('CHARGILY_WEBHOOK_SECRET'),
        'mode'           => env('CHARGILY_MODE', 'test'),
        'base_url'       => env('CHARGILY_MODE', 'test') === 'live'
            ? 'https://pay.chargily.net/api/v2'
            : 'https://pay.chargily.net/test/api/v2',
        'success_url'    => env('APP_URL', 'http://localhost') . '/provider/credits/success',
        'failure_url'    => env('APP_URL', 'http://localhost') . '/provider/credits/failure',
        'webhook_url'    => env('APP_URL', 'http://localhost') . '/webhooks/chargily',
    ],
];
