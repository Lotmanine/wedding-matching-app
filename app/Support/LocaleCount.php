<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Durations and counts used inside translated sentences.
 *
 * French keeps the bare number ("2" in ":hours heures"). Arabic changes the
 * noun with the number (ساعتين، 3 ساعات، 14 يوما), so the Arabic sentence
 * receives the number and its unit together.
 *
 * $standalone: the value is shown on its own (a figure with a caption, or
 * after a colon) rather than inside a sentence. Only the dual differs:
 * "مهلة الرد: ساعتان" against "مهلة ساعتين للرد".
 */
final class LocaleCount
{
    /** [one, two inside a sentence, two standalone, plural for 3–10, singular used from 11] */
    private const AR = [
        'hours'  => ['ساعة واحدة', 'ساعتين', 'ساعتان', 'ساعات', 'ساعة'],
        'days'   => ['يوم واحد', 'يومين', 'يومان', 'أيام', 'يوما'],
        'rounds' => ['جولة واحدة', 'جولتين', 'جولتان', 'جولات', 'جولة'],
    ];

    public static function hours(int $n, bool $standalone = false): string
    {
        return self::format('hours', $n, $standalone);
    }

    public static function days(int $n, bool $standalone = false): string
    {
        return self::format('days', $n, $standalone);
    }

    public static function rounds(int $n, bool $standalone = false): string
    {
        return self::format('rounds', $n, $standalone);
    }

    private static function format(string $unit, int $n, bool $standalone): string
    {
        if (app()->getLocale() !== 'ar') {
            return (string) $n;
        }

        [$one, $two, $twoStandalone, $few, $many] = self::AR[$unit];
        $lastTwo = $n % 100;

        return match (true) {
            $n === 1                        => $one,
            $n === 2                        => $standalone ? $twoStandalone : $two,
            $lastTwo >= 3 && $lastTwo <= 10 => $n . ' ' . $few,
            default                         => $n . ' ' . $many,
        };
    }
}
