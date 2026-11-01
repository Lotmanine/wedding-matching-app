<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Setting;

/**
 * Reads/writes the admin-configurable payment configuration, stored in the
 * generic settings table under the `payments.*` keys. Two methods are
 * supported:
 *
 *  - online_chargily   : the optional Chargily Pay card redirect flow.
 *  - offline_transfer  : bank transfer; the provider declares a
 *                         reference and an admin confirms receipt manually.
 *
 * Bank details shown on the offline checkout page are also stored
 * here so a super-admin can edit them without touching code or .env.
 */
class PaymentSettings
{
    public const METHOD_ONLINE = 'online_chargily';
    public const METHOD_OFFLINE = 'offline_transfer';

    public function onlineEnabled(): bool
    {
        return (bool) Setting::get('payments.online_enabled', true);
    }

    public function offlineEnabled(): bool
    {
        return (bool) Setting::get('payments.offline_enabled', false);
    }

    /** @return list<string> */
    public function enabledMethods(): array
    {
        $methods = [];
        if ($this->onlineEnabled()) {
            $methods[] = self::METHOD_ONLINE;
        }
        if ($this->offlineEnabled()) {
            $methods[] = self::METHOD_OFFLINE;
        }

        return $methods;
    }

    public function isMethodEnabled(string $method): bool
    {
        return in_array($method, $this->enabledMethods(), true);
    }

    /**
     * Bank details shown to the provider for an offline bank transfer.
     *
     * @return array<string, string>
     */
    public function offlineDetails(): array
    {
        return [
            'bank_name'        => (string) Setting::get('payments.offline.bank_name', ''),
            'account_holder'   => (string) Setting::get('payments.offline.account_holder', ''),
            'account_number'              => (string) Setting::get('payments.offline.account_number', ''),
            'secondary_account_number'       => (string) Setting::get('payments.offline.secondary_account_number', ''),
            'secondary_account_key'          => (string) Setting::get('payments.offline.secondary_account_key', ''),
            'instructions'     => (string) Setting::get('payments.offline.instructions', ''),
        ];
    }

    /**
     * Persist the payment configuration from an admin settings form.
     *
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        Setting::set('payments.online_enabled', ! empty($data['online_enabled']) ? '1' : '0', 'bool');
        Setting::set('payments.offline_enabled', ! empty($data['offline_enabled']) ? '1' : '0', 'bool');

        foreach ([
            'bank_name', 'account_holder', 'account_number', 'secondary_account_number', 'secondary_account_key', 'instructions',
        ] as $field) {
            Setting::set('payments.offline.' . $field, (string) ($data[$field] ?? ''), 'string');
        }
    }

    // ─── Chargily gateway credentials (admin-editable) ──────────────
    //
    // Secrets are stored ENCRYPTED in the settings table (Crypt) so a DB dump
    // doesn't leak them. Empty DB value → fall back to .env via config(),
    // which keeps existing deployments working untouched.

    public function chargilyMode(): string
    {
        $mode = (string) Setting::get('payments.chargily.mode', '');

        return in_array($mode, ['test', 'live'], true)
            ? $mode
            : (string) config('wedding.chargily.mode', 'test');
    }

    public function chargilyBaseUrl(): string
    {
        return $this->chargilyMode() === 'live'
            ? 'https://pay.chargily.net/api/v2'
            : 'https://pay.chargily.net/test/api/v2';
    }

    public function chargilySecretKey(): string
    {
        return $this->decryptedSetting('payments.chargily.secret_key')
            ?? (string) config('wedding.chargily.secret_key', '');
    }

    public function chargilyWebhookSecret(): string
    {
        return $this->decryptedSetting('payments.chargily.webhook_secret')
            ?? (string) config('wedding.chargily.webhook_secret', '');
    }

    /**
     * Whether each secret currently comes from the DB (true) or .env (false),
     * plus a masked preview — used by the admin UI, never the raw values.
     *
     * @return array<string, array{source: string, masked: string}>
     */
    public function chargilyKeyStatus(): array
    {
        $status = [];
        foreach (['secret_key' => $this->chargilySecretKey(), 'webhook_secret' => $this->chargilyWebhookSecret()] as $name => $value) {
            $fromDb = $this->decryptedSetting('payments.chargily.' . $name) !== null;
            $status[$name] = [
                'source' => $value === '' ? 'absent' : ($fromDb ? 'db' : 'env'),
                'masked' => $value === '' ? '' : '••••••••' . substr($value, -4),
            ];
        }

        return $status;
    }

    /**
     * Save Chargily credentials from the admin form. Blank secret fields mean
     * "keep the current value" so the form never has to echo secrets back.
     *
     * @param array<string, mixed> $data
     */
    public function saveChargily(array $data): void
    {
        $mode = (string) ($data['chargily_mode'] ?? 'test');
        Setting::set('payments.chargily.mode', in_array($mode, ['test', 'live'], true) ? $mode : 'test', 'string');

        foreach (['secret_key' => 'chargily_secret_key', 'webhook_secret' => 'chargily_webhook_secret'] as $key => $field) {
            $value = trim((string) ($data[$field] ?? ''));
            if ($value !== '') {
                Setting::set('payments.chargily.' . $key, \Illuminate\Support\Facades\Crypt::encryptString($value), 'string');
            }
        }
    }

    private function decryptedSetting(string $key): ?string
    {
        $raw = Setting::get($key);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null; // corrupt / re-keyed APP_KEY — behave as unset
        }
    }
}
