<?php

declare(strict_types=1);

return [

    'credits' => [
        'unlock_cost' => (int) env('WEDDING_UNLOCK_COST', 1),
    ],

    'matching' => [
        'round_timeout_minutes' => (int) env('WEDDING_ROUND_TIMEOUT_MINUTES', 120),
        'providers_per_round' => (int) env('WEDDING_PROVIDERS_PER_ROUND', 3),
        'max_rounds' => 3,
    ],

    'privacy' => [
        'retention_days' => (int) env('WEDDING_RETENTION_DAYS', 14),
    ],

    // Public demo: fills the database with sample data, enables the test
    // checkout and shows these logins on the login pages.
    'demo' => [
        'enabled' => (bool) env('WEDDING_DEMO_MODE', false),
        'accounts' => [
            'provider'    => ['label' => 'Provider (New York)', 'email' => 'demo.provider@example.com', 'password' => 'demo1234'],
            'provider_fr' => ['label' => 'Provider (Paris)', 'email' => 'demo.prestataire@example.com', 'password' => 'demo1234'],
            'admin'       => ['label' => 'Administrator', 'email' => 'demo.admin@example.com', 'password' => 'demo1234'],
        ],
    ],

    'whatsapp' => [
        'link_prefix' => 'https://wa.me/',
    ],

    'n8n' => [
        // Shared secret guarding the public n8n -> Laravel error webhook.
        // When set, the request must carry a matching X-Wedding-Token header.
        // Reuses the same secret n8n already holds (WEDDING_WEBHOOK_SECRET).
        'webhook_secret' => env('WEDDING_WEBHOOK_SECRET'),
    ],

    'chargily' => [
        'api_key' => env('CHARGILY_API_KEY'),
        'secret_key' => env('CHARGILY_SECRET_KEY'),
        'webhook_secret' => env('CHARGILY_WEBHOOK_SECRET'),
        'mode' => env('CHARGILY_MODE', 'test'),
        'base_url' => env('CHARGILY_MODE', 'test') === 'live'
            ? 'https://pay.chargily.net/api/v2'
            : 'https://pay.chargily.net/test/api/v2',
        'success_url' => env('APP_URL', 'http://localhost').'/provider/credits/success',
        'failure_url' => env('APP_URL', 'http://localhost').'/provider/credits/failure',
        'webhook_url' => env('APP_URL', 'http://localhost').'/webhooks/chargily',
    ],
];
