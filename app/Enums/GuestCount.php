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
            self::Under100    => __('Moins de 100 invités'),
            self::From100To200 => __('100 – 200 invités'),
            self::From200To300 => __('200 – 300 invités'),
            self::From300To500 => __('300 – 500 invités'),
            self::Over500     => __('Plus de 500 invités'),
        };
    }
}
