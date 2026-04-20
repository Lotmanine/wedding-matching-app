<?php

declare(strict_types=1);

namespace App\Enums;

enum RequestStatus: string
{
    case New             = 'new';
    case Matching        = 'matching';
    case Round1          = 'round1';
    case Round2          = 'round2';
    case Round3          = 'round3';
    case OpenMarketplace = 'open_marketplace';
    case Accepted        = 'accepted';
    case Expired         = 'expired';
    case Deleted         = 'deleted';
}
