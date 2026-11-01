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
                        'label_en'  => 'Accept',
                        'label_fr'  => 'Accepter',
                    ],
                    [
                        'id'        => SignedButtonPayload::encode($sr->service_uid, $user->id, SignedButtonPayload::INTENT_REFUSE),
                        'intent'    => 'refuse',
                        'label_en'  => 'Refuse',
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
            // District is optional on the couple form: fall back to the city so the
            // provider's message always names a place.
            'city'               => (string) ($wr->couple_district ?: (\App\Models\City::where('code', $wr->couple_city_code)->value('name') ?? '')),
            'preferred_language' => 'fr', // couple form only collects French copy for now
        ];
    }

    public static function servicePayload(ServiceRequest $sr): array
    {
        $wr = $sr->wedRequest;

        return [
            'type'             => $sr->service_type?->value ?? (string) $sr->service_type,
            'budget_hint'      => self::budgetHintMidpoint($wr->budget_range?->value ?? null),
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
            return 'en';
        }

        // Prefer en if available, then fr, then first listed
        if (in_array('en', $langs, true)) {
            return 'en';
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
            'under_10k'        => 5_000,
            'from_10k_to_20k'  => 15_000,
            'from_20k_to_35k'  => 27_500,
            'from_35k_to_50k'  => 42_500,
            'from_50k_to_100k' => 75_000,
            'over_100k'        => 125_000,
            default            => null,
        };
    }
}
