<?php

declare(strict_types=1);

namespace App\Services\Whatsapp;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

/**
 * Reads/writes the admin-configurable Meta WhatsApp Cloud API configuration,
 * stored in the generic settings table under the `whatsapp.*` keys.
 *
 * Laravel never calls Meta itself: the n8n `wedding-whatsapp` workflow fetches
 * these values through GET /api/v1/n8n/whatsapp-config, so a super-admin can
 * plug the Meta account in from /admin/whatsapp without touching n8n.
 *
 * Secrets are stored ENCRYPTED (Crypt) so a DB dump doesn't leak them.
 */
class WhatsappSettings
{
    /** Encrypted keys. The phone number ID is an identifier, not a secret. */
    public const SECRETS = ['access_token', 'verify_token', 'app_secret'];

    /** Raw state of the admin switch, regardless of whether keys are present. */
    public function switchedOn(): bool
    {
        return (bool) Setting::get('whatsapp.enabled', false);
    }

    public function phoneNumberId(): string
    {
        return (string) Setting::get('whatsapp.phone_number_id', '');
    }

    public function accessToken(): string
    {
        return $this->decryptedSetting('whatsapp.access_token') ?? '';
    }

    public function verifyToken(): string
    {
        return $this->decryptedSetting('whatsapp.verify_token') ?? '';
    }

    public function appSecret(): string
    {
        return $this->decryptedSetting('whatsapp.app_secret') ?? '';
    }

    /** Everything needed to SEND is present (phone number ID + access token). */
    public function canSend(): bool
    {
        return $this->phoneNumberId() !== '' && $this->accessToken() !== '';
    }

    /** Effective state: n8n only sends when the switch is on AND keys exist. */
    public function isActive(): bool
    {
        return $this->switchedOn() && $this->canSend();
    }

    /**
     * Whether each secret is set, plus a masked preview — used by the admin
     * UI, never the raw values.
     *
     * @return array<string, array{set: bool, masked: string}>
     */
    public function secretStatus(): array
    {
        $values = [
            'access_token' => $this->accessToken(),
            'verify_token' => $this->verifyToken(),
            'app_secret'   => $this->appSecret(),
        ];

        $status = [];
        foreach ($values as $name => $value) {
            $status[$name] = [
                'set'    => $value !== '',
                'masked' => $value === '' ? '' : '••••••••' . substr($value, -4),
            ];
        }

        return $status;
    }

    /**
     * Persist the configuration from the admin form. Blank secret fields mean
     * "keep the current value" so the form never has to echo secrets back.
     *
     * @param array<string, mixed> $data
     */
    public function save(array $data): void
    {
        Setting::set('whatsapp.enabled', ! empty($data['enabled']) ? '1' : '0', 'bool');
        Setting::set('whatsapp.phone_number_id', trim((string) ($data['phone_number_id'] ?? '')), 'string');

        foreach (self::SECRETS as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            if ($value !== '') {
                Setting::set('whatsapp.' . $key, Crypt::encryptString($value), 'string');
            }
        }
    }

    /**
     * Payload served to n8n. `enabled` is the EFFECTIVE state, so the
     * workflow has a single flag to test before calling Meta.
     *
     * @return array{enabled: bool, phone_number_id: string, access_token: string, verify_token: string, app_secret: string}
     */
    public function forN8n(): array
    {
        return [
            'enabled'         => $this->isActive(),
            'phone_number_id' => $this->phoneNumberId(),
            'access_token'    => $this->accessToken(),
            'verify_token'    => $this->verifyToken(),
            'app_secret'      => $this->appSecret(),
        ];
    }

    private function decryptedSetting(string $key): ?string
    {
        $raw = Setting::get($key);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            return Crypt::decryptString($raw);
        } catch (\Throwable) {
            return null; // corrupt / re-keyed APP_KEY — behave as unset
        }
    }
}
