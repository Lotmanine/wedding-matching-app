<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Makes the values saved in /admin/settings effective: they override the
 * defaults of config/wedding.php, so every `config('wedding.…')` reader follows
 * the admin's choice. Applied at boot and before each queued job (workers
 * are long-lived and would otherwise keep the values they started with).
 */
class SettingsConfig
{
    /** setting key => config key */
    private const MAP = [
        'credits.unlock_cost'            => 'wedding.credits.unlock_cost',
        'matching.round_timeout_minutes' => 'wedding.matching.round_timeout_minutes',
        'matching.providers_per_round'   => 'wedding.matching.providers_per_round',
        'privacy.retention_days'           => 'wedding.privacy.retention_days',
    ];

    public static function apply(): void
    {
        foreach (self::MAP as $setting => $config) {
            $value = Setting::get($setting);

            // Ignore missing or nonsensical values rather than break matching.
            if (is_numeric($value) && (int) $value > 0) {
                config([$config => (int) $value]);
            }
        }
    }
}
