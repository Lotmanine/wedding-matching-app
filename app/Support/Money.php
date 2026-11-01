<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\City;

/**
 * Whole-unit prices in the currency of the city they belong to.
 *
 * A city in France is priced in EUR, any other city in USD. Without a city
 * (public pages, a form before the couple picks one) the visitor's language
 * decides: French reads EUR, anything else USD.
 */
final class Money
{
    private const NBSP = "\u{00A0}";

    private const SYMBOLS = ['USD' => '$', 'EUR' => '€'];

    /** @return 'USD'|'EUR' */
    public static function currencyForCity(?string $cityCode): string
    {
        $country = $cityCode ? self::countryOf($cityCode) : null;

        if ($country !== null) {
            return $country === 'FR' ? 'EUR' : 'USD';
        }

        return app()->getLocale() === 'fr' ? 'EUR' : 'USD';
    }

    public static function format(int $amount, string $currency): string
    {
        return $currency === 'EUR'
            ? number_format($amount, 0, ',', self::NBSP) . self::NBSP . self::symbol($currency)
            : self::symbol($currency) . number_format($amount);
    }

    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[$currency] ?? $currency;
    }

    /** A list view prices one row per request: look each city up only once. */
    private static function countryOf(string $cityCode): ?string
    {
        return once(fn () => City::where('code', $cityCode)->value('country'));
    }
}
