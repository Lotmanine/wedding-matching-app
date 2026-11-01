<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Purchase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ChargilyClient
{
    public function __construct(private readonly PaymentSettings $settings)
    {
    }

    public function createCheckout(Purchase $purchase): array
    {
        $purchase->loadMissing(['user', 'unlockPack']);

        $payload = [
            'amount'            => (int) $purchase->amount,
            'currency'          => 'dzd',
            'success_url'       => config('wedding.chargily.success_url') . '?purchase=' . $purchase->purchase_uid,
            'failure_url'       => config('wedding.chargily.failure_url') . '?purchase=' . $purchase->purchase_uid,
            'webhook_endpoint'  => config('wedding.chargily.webhook_url'),
            'description'       => 'Wedding Matching App — ' . ($purchase->unlockPack?->name ?? 'Pack de crédits'),
            'metadata'          => [
                'purchase_uid' => $purchase->purchase_uid,
                'user_id'      => $purchase->user_id,
            ],
            'customer'          => [
                'name'    => $purchase->user?->name,
                'email'   => $purchase->user?->email,
                'phone'   => $purchase->user?->phone,
            ],
        ];

        try {
            $response = Http::withToken($this->settings->chargilySecretKey())
                ->acceptJson()
                ->timeout(15)
                ->retry(2, 500, throw: false)
                ->post($this->settings->chargilyBaseUrl() . '/checkouts', $payload);
        } catch (ConnectionException $e) {
            Log::error('Chargily: connection error creating checkout', ['error' => $e->getMessage()]);
            throw new RuntimeException('Chargily indisponible — merci de réessayer dans un instant.', previous: $e);
        }

        if (! $response->successful()) {
            Log::error('Chargily: checkout creation failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new RuntimeException('Chargily a refusé la demande de paiement (' . $response->status() . ').');
        }

        $body = $response->json();

        return [
            'id'           => $body['id']           ?? ($body['data']['id'] ?? null),
            'checkout_url' => $body['checkout_url'] ?? ($body['data']['checkout_url'] ?? null),
            'raw'          => $body,
        ];
    }

    public function verifyWebhookSignature(?string $signature, string $payload): bool
    {
        if (empty($signature)) {
            return false;
        }

        $secret = $this->settings->chargilyWebhookSecret();
        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }

    public function getCheckoutStatus(string $checkoutId): array
    {
        $response = Http::withToken($this->settings->chargilySecretKey())
            ->acceptJson()
            ->timeout(10)
            ->get($this->settings->chargilyBaseUrl() . '/checkouts/' . $checkoutId);

        return $response->successful() ? (array) $response->json() : [];
    }
}
