<?php

declare(strict_types=1);

namespace App\Enums;

enum BudgetRange: string
{
    case Under500k    = 'under_500k';
    case From500kTo1m = 'from_500k_to_1m';
    case From1mTo2m   = 'from_1m_to_2m';
    case From2mTo3m   = 'from_2m_to_3m';
    case From3mTo5m   = 'from_3m_to_5m';
    case Over5m       = 'over_5m';

    public function label(): string
    {
        return match($this) {
            self::Under500k    => __('Moins de 500 000 DA'),
            self::From500kTo1m => __('500 000 – 1 000 000 DA'),
            self::From1mTo2m   => __('1 000 000 – 2 000 000 DA'),
            self::From2mTo3m   => __('2 000 000 – 3 000 000 DA'),
            self::From3mTo5m   => __('3 000 000 – 5 000 000 DA'),
            self::Over5m       => __('Plus de 5 000 000 DA'),
        };
    }
}
