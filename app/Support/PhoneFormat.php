<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The phone numbers the platform accepts: US and French mobiles in E.164.
 */
final class PhoneFormat
{
    public const REGEX = '/^\+(1[2-9]\d{9}|33[67]\d{8})$/';
}
