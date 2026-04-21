<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InsufficientCreditsException extends RuntimeException
{
    public function __construct(public readonly int $required, public readonly int $available)
    {
        parent::__construct("Insufficient credits: required {$required}, available {$available}");
    }
}
