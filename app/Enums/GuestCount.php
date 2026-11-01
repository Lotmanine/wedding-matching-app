<?php

declare(strict_types=1);

namespace App\Enums;

enum GuestCount: string
{
    case Under100    = 'under_100';
    case From100To200 = 'from_100_to_200';
    case From200To300 = 'from_200_to_300';
    case From300To500 = 'from_300_to_500';
    case Over500     = 'over_500';

    public function label(): string
    {
        return match($this) {
            self::Under100    => __('Under 100 guests'),
            self::From100To200 => __('100 – 200 guests'),
            self::From200To300 => __('200 – 300 guests'),
            self::From300To500 => __('300 – 500 guests'),
            self::Over500     => __('Over 500 guests'),
        };
    }
}
