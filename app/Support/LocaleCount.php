<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Durations and counts used inside translated sentences.
 *
 * French keeps the bare number ("2" in ":hours heures"). Arabic changes the
 * noun with the number (ساعتين، 3 ساعات، 14 يومًا), so the Arabic sentence
 * receives the number and its unit together.
 */
final class LocaleCount
{
    /** [one, two, plural for 3–10, singular used from 11] */
    private const AR = [
        'hours'  => ['ساعة واحدة', 'ساعتين', 'ساعات', 'ساعة'],
        'days'   => ['يوم واحد', 'يومين', 'أيام', 'يومًا'],
        'rounds' => ['جولة واحدة', 'جولتين', 'جولات', 'جولة'],
    ];

    public static function hours(int $n): string
    {
        return self::format('hours', $n);
    }

    public static function days(int $n): string
    {
        return self::format('days', $n);
    }

    public static function rounds(int $n): string
    {
        return self::format('rounds', $n);
    }

    private static function format(string $unit, int $n): string
    {
        if (app()->getLocale() !== 'ar') {
            return (string) $n;
        }

        [$one, $two, $few, $many] = self::AR[$unit];
        $lastTwo = $n % 100;

        return match (true) {
            $n === 1                        => $one,
            $n === 2                        => $two,
            $lastTwo >= 3 && $lastTwo <= 10 => $n . ' ' . $few,
            default                         => $n . ' ' . $many,
        };
    }
}
