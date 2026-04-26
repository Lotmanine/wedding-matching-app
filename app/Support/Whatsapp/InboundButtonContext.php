<?php

declare(strict_types=1);

namespace App\Support\Whatsapp;

use App\Models\ServiceRequest;
use App\Models\User;

final class InboundButtonContext
{
    public function __construct(
        public readonly ServiceRequest $serviceRequest,
        public readonly User $provider,
        public readonly string $intent,
    ) {
    }

    public function isAccept(): bool
    {
        return $this->intent === SignedButtonPayload::INTENT_ACCEPT;
    }

    public function isRefuse(): bool
    {
        return $this->intent === SignedButtonPayload::INTENT_REFUSE;
    }
}
