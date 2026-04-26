<?php

declare(strict_types=1);

namespace App\Support\Whatsapp;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

/**
 * Encodes/decodes the opaque payload sent in WhatsApp quick-reply buttons.
 * Tuple: {service_request_uid, provider_id, intent, nonce, issued_at}.
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

    /** Replay-protection bound for old buttons taps long after the round closed. */
    public const MAX_AGE_DAYS = 7;

    private const VALID_INTENTS = [self::INTENT_ACCEPT, self::INTENT_REFUSE];

    /** Statuses where a provider's accept/refuse is still meaningful. */
    private const OPEN_STATUSES = [
        'round1',
        'round2',
        'round3',
        'open_marketplace',
    ];

    public static function encode(string $serviceRequestUid, int $providerId, string $intent): string
    {
        if (! in_array($intent, self::VALID_INTENTS, true)) {
            throw new InvalidArgumentException("Unknown intent: {$intent}");
        }

        // Positional CSV: uid|providerId|intent|issued_at_unix.
        // Laravel's Crypt::encryptString uses a fresh random IV per call, so two encodes
        // of the same input always produce different ciphertexts — no separate nonce needed.
        $issuedAt = (string) now()->timestamp;
        $packed = $serviceRequestUid . '|' . $providerId . '|' . $intent . '|' . $issuedAt;

        return Crypt::encryptString($packed);
    }

    /**
     * @return array{service_request_uid:string, provider_id:int, intent:string, issued_at:int}
     */
    public static function decode(string $token): array
    {
        try {
            $packed = Crypt::decryptString($token);
        } catch (DecryptException $e) {
            throw new InvalidArgumentException('Invalid or tampered token', previous: $e);
        }

        $parts = explode('|', $packed);
        if (count($parts) !== 4) {
            throw new InvalidArgumentException('Malformed payload structure');
        }

        [$uid, $pid, $intent, $issuedAt] = $parts;

        if (! in_array($intent, self::VALID_INTENTS, true)) {
            throw new InvalidArgumentException('Unknown intent in token');
        }

        return [
            'service_request_uid' => $uid,
            'provider_id'         => (int) $pid,
            'intent'              => $intent,
            'issued_at'           => (int) $issuedAt,
        ];
    }

    /**
     * Decode + validate against current state and the sender's E.164 phone.
     *
     * @throws InboundButtonException
     */
    public static function decodeAndValidate(string $token, string $fromPhoneE164): InboundButtonContext
    {
        try {
            $payload = self::decode($token);
        } catch (InvalidArgumentException $e) {
            throw InboundButtonException::tampered($e->getMessage());
        }

        // Replay window
        if ($payload['issued_at'] <= 0
            || now()->diffInDays(\Carbon\Carbon::createFromTimestamp($payload['issued_at']), absolute: true) > self::MAX_AGE_DAYS) {
            throw InboundButtonException::expired();
        }

        $sr = ServiceRequest::where('service_uid', $payload['service_request_uid'])->first();
        if (! $sr) {
            throw InboundButtonException::tampered('service_request not found');
        }

        $provider = User::find($payload['provider_id']);
        if (! $provider || ! $provider->hasRole('provider')) {
            throw InboundButtonException::tampered('provider not found');
        }

        // Sender phone must match this provider's whatsapp number.
        $providerPhone = self::normalizeE164((string) $provider->whatsapp);
        $senderPhone   = self::normalizeE164($fromPhoneE164);
        if ($providerPhone === '' || $providerPhone !== $senderPhone) {
            throw InboundButtonException::mismatchedPhone();
        }

        $statusValue = $sr->status?->value ?? (string) $sr->status;

        // Round closed — no longer accepting decisions.
        if (! in_array($statusValue, self::OPEN_STATUSES, true)) {
            // Distinguish "already won" from "closed for other reason"
            if ($sr->unlocked_by_user_id !== null && $sr->unlocked_by_user_id !== $provider->id) {
                throw InboundButtonException::alreadyWon();
            }
            throw InboundButtonException::roundClosed();
        }

        // Already won by a different provider (race)
        if ($sr->unlocked_by_user_id !== null && $sr->unlocked_by_user_id !== $provider->id) {
            throw InboundButtonException::alreadyWon();
        }

        return new InboundButtonContext($sr, $provider, $payload['intent']);
    }

    private static function normalizeE164(string $raw): string
    {
        $stripped = preg_replace('/[^\d+]/', '', $raw) ?? '';
        if ($stripped === '') {
            return '';
        }

        return str_starts_with($stripped, '+') ? $stripped : ('+' . ltrim($stripped, '+'));
    }
}
