<?php

declare(strict_types=1);

namespace App\Enums;

use App\Support\Money;

enum BudgetRange: string
{
    case Under10k      = 'under_10k';
    case From10kTo20k  = 'from_10k_to_20k';
    case From20kTo35k  = 'from_20k_to_35k';
    case From35kTo50k  = 'from_35k_to_50k';
    case From50kTo100k = 'from_50k_to_100k';
    case Over100k      = 'over_100k';

    /**
     * @param  string|null  $currency  The wedding's currency; without one, the
     *                                 default for the visitor's language.
     */
    public function label(?string $currency = null): string
    {
        $currency ??= Money::currencyForCity(null);
        $money = fn (int $amount) => Money::format($amount, $currency);

        return match($this) {
            self::Under10k      => __('Under :amount', ['amount' => $money(10_000)]),
            self::From10kTo20k  => $money(10_000) . ' – ' . $money(20_000),
            self::From20kTo35k  => $money(20_000) . ' – ' . $money(35_000),
            self::From35kTo50k  => $money(35_000) . ' – ' . $money(50_000),
            self::From50kTo100k => $money(50_000) . ' – ' . $money(100_000),
            self::Over100k      => __('Over :amount', ['amount' => $money(100_000)]),
        };
    }
}
