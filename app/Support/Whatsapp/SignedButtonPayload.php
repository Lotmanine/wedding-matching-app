<?php

declare(strict_types=1);

namespace App\Support\Whatsapp;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

/**
 * Encodes/decodes the opaque payload sent in WhatsApp quick-reply buttons.
 * Tuple: {service_request_uid, provider_id, intent, nonce}.
 *
 * Encrypted via Laravel Crypt::encryptString (AES-256-CBC + MAC). The encoded
 * token must fit Meta's 256-char quick-reply payload limit — covered in tests.
 *
 * Workflow 3 (inbound) decodes the payload to identify the responding provider.
 */
class SignedButtonPayload
{
    public const INTENT_ACCEPT = 'accept';
    public const INTENT_REFUSE = 'refuse';

    private const VALID_INTENTS = [self::INTENT_ACCEPT, self::INTENT_REFUSE];

    public static function encode(string $serviceRequestUid, int $providerId, string $intent): string
    {
        if (! in_array($intent, self::VALID_INTENTS, true)) {
            throw new InvalidArgumentException("Unknown intent: {$intent}");
        }

        // Compact CSV: positional order is uid|providerId|intent|nonce.
        // Crypt::encryptString already gives us an authenticated, base64-safe token.
        $nonce = bin2hex(random_bytes(4));
        $packed = $serviceRequestUid . '|' . $providerId . '|' . $intent . '|' . $nonce;

        return Crypt::encryptString($packed);
    }

    /**
     * @return array{service_request_uid:string, provider_id:int, intent:string, nonce:string}
     */
    public static function decode(string $token): array
    {
        try {
            $packed = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            throw new InvalidArgumentException('Invalid or tampered token', previous: $e);
        }

        $parts = explode('|', $packed, 4);
        if (count($parts) !== 4) {
            throw new InvalidArgumentException('Malformed payload structure');
        }

        [$uid, $pid, $intent, $nonce] = $parts;

        if (! in_array($intent, self::VALID_INTENTS, true)) {
            throw new InvalidArgumentException('Unknown intent in token');
        }

        return [
            'service_request_uid' => $uid,
            'provider_id'         => (int) $pid,
            'intent'              => $intent,
            'nonce'               => $nonce,
        ];
    }
}
