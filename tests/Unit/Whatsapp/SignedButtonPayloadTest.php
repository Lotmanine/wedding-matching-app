<?php

declare(strict_types=1);

namespace Tests\Unit\Whatsapp;

use App\Support\Whatsapp\SignedButtonPayload;
use InvalidArgumentException;
use Tests\TestCase;

class SignedButtonPayloadTest extends TestCase
{
    public function test_encode_then_decode_roundtrip(): void
    {
        $token = SignedButtonPayload::encode('SRV-20260422-A001', 12, SignedButtonPayload::INTENT_ACCEPT);
        $decoded = SignedButtonPayload::decode($token);

        $this->assertSame('SRV-20260422-A001', $decoded['service_request_uid']);
        $this->assertSame(12, $decoded['provider_id']);
        $this->assertSame('accept', $decoded['intent']);
        $this->assertGreaterThan(0, $decoded['issued_at']);
    }

    public function test_token_fits_meta_quick_reply_limit(): void
    {
        $token = SignedButtonPayload::encode('SRV-20260422-A001', 999999, SignedButtonPayload::INTENT_REFUSE);

        $this->assertLessThanOrEqual(256, strlen($token), 'Token exceeds Meta quick-reply payload 256-char limit');
    }

    public function test_tampered_token_throws(): void
    {
        $token = SignedButtonPayload::encode('SRV-20260422-A001', 12, 'accept');

        // Flip a few characters mid-string
        $tampered = substr($token, 0, 30) . 'XYZ' . substr($token, 33);

        $this->expectException(InvalidArgumentException::class);
        SignedButtonPayload::decode($tampered);
    }

    public function test_unknown_intent_rejected_on_encode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SignedButtonPayload::encode('SRV-X', 1, 'demolish');
    }

    public function test_different_iv_produce_different_tokens(): void
    {
        $a = SignedButtonPayload::encode('SRV-X', 1, 'accept');
        $b = SignedButtonPayload::encode('SRV-X', 1, 'accept');

        $this->assertNotSame($a, $b, 'Crypt randomises the IV per call, so two encodes must produce different tokens');
    }
}
