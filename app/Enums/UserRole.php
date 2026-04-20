<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Couple   = 'couple';
    case Provider = 'provider';
    case Admin    = 'admin';
}
