<?php

declare(strict_types=1);

namespace App\Support\Whatsapp;

use RuntimeException;

class InboundButtonException extends RuntimeException
{
    public const REASON_TAMPERED         = 'tampered';
    public const REASON_MISMATCHED_PHONE = 'mismatched_phone';
    public const REASON_ROUND_CLOSED     = 'round_closed';
    public const REASON_ALREADY_WON      = 'already_won';
    public const REASON_EXPIRED          = 'expired';

    public function __construct(public readonly string $reason, string $detail = '')
    {
        parent::__construct($detail !== '' ? "{$reason}: {$detail}" : $reason);
    }

    public static function tampered(string $detail = ''): self
    {
        return new self(self::REASON_TAMPERED, $detail);
    }

    public static function mismatchedPhone(): self
    {
        return new self(self::REASON_MISMATCHED_PHONE);
    }

    public static function roundClosed(): self
    {
        return new self(self::REASON_ROUND_CLOSED);
    }

    public static function alreadyWon(): self
    {
        return new self(self::REASON_ALREADY_WON);
    }

    public static function expired(): self
    {
        return new self(self::REASON_EXPIRED);
    }
}
