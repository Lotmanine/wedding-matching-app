<?php

declare(strict_types=1);

namespace App\Support\Whatsapp;

use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Provider-agnostic builder. Outputs only domain data — never knows about
 * Meta / Twilio / 360dialog template shapes. n8n nodes 5-7 do the BSP-specific
 * mapping; swapping providers means touching n8n only.
 */
class RecipientPayloadBuilder
{
    /**
     * Build the recipient blocks for round-teaser / marketplace-broadcast events.
     *
     * @param  Collection<int, User>  $providers
     * @return array<int, array<string, mixed>>
     */
    public static function forProviders(ServiceRequest $sr, Collection $providers): array
    {
        return $providers->map(function (User $user) use ($sr) {
            $profile = $user->providerProfile;

            return [
                'provider_id'        => $user->id,
                'phone_e164'         => self::normalizeE164($user->whatsapp ?: $user->phone),
                'preferred_language' => self::pickLanguage($profile),
                'buttons' => [
                    [
                        'id'        => SignedButtonPayload::encode($sr->service_uid, $user->id, SignedButtonPayload::INTENT_ACCEPT),
                        'intent'    => 'accept',
                        'label_ar'  => 'قبول',
                        'label_fr'  => 'Accepter',
                    ],
                    [
                        'id'        => SignedButtonPayload::encode($sr->service_uid, $user->id, SignedButtonPayload::INTENT_REFUSE),
                        'intent'    => 'refuse',
                        'label_ar'  => 'رفض',
                        'label_fr'  => 'Refuser',
                    ],
                ],
            ];
        })->values()->all();
    }

    public static function couplePayload(ServiceRequest $sr): array
    {
        $wr = $sr->wedRequest;

        return [
            'first_name'         => (string) ($wr->couple_first_name ?? ''),
            'phone_e164'         => self::normalizeE164($wr->couple_whatsapp ?? ''),
            // Commune is optional on the couple form: fall back to the wilaya so the
            // provider's message never reads "cherche un prestataire à -".
            'city'               => (string) ($wr->couple_commune ?: (\App\Models\Wilaya::where('code', $wr->couple_wilaya_code)->value('name_fr') ?? '')),
            'preferred_language' => 'fr', // couple form only collects French copy for now
        ];
    }

    public static function servicePayload(ServiceRequest $sr): array
    {
        $wr = $sr->wedRequest;

        return [
            'type'             => $sr->service_type?->value ?? (string) $sr->service_type,
            'budget_hint_dzd'  => self::budgetHintMidpoint($wr->budget_range?->value ?? null),
            'event_date'       => $wr->wedding_date?->toDateString(),
        ];
    }

    public static function normalizeE164(?string $raw): string
    {
        if (! $raw) {
            return '';
        }
        $stripped = preg_replace('/[^\d+]/', '', $raw) ?? '';

        return str_starts_with($stripped, '+') ? $stripped : ('+' . ltrim($stripped, '+'));
    }

    private static function pickLanguage(?ProviderProfile $profile): string
    {
        $langs = (array) ($profile?->languages ?? []);
        if (empty($langs)) {
            return 'ar';
        }

        // Prefer ar if available, then fr, then first listed
        if (in_array('ar', $langs, true)) {
            return 'ar';
        }
        if (in_array('fr', $langs, true)) {
            return 'fr';
        }

        return (string) $langs[0];
    }

    private static function budgetHintMidpoint(?string $range): ?int
    {
        // Keys must follow App\Enums\BudgetRange: a range missing here reaches
        // the provider's WhatsApp message as an empty budget.
        return match ($range) {
            'under_500k'      => 250_000,
            'from_500k_to_1m' => 750_000,
            'from_1m_to_2m'   => 1_500_000,
            'from_2m_to_3m'   => 2_500_000,
            'from_3m_to_5m'   => 4_000_000,
            'over_5m'         => 7_000_000,
            default           => null,
        };
    }
}
