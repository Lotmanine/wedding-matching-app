<?php

declare(strict_types=1);

namespace App\Services\Matching\Results;

use App\Models\ServiceRequest;

final class RefuseResult
{
    public const STATUS_SUCCESS         = 'success';
    public const STATUS_ALREADY_REFUSED = 'already_refused';
    public const STATUS_NOT_ELIGIBLE    = 'not_eligible';

    private function __construct(
        public readonly string $status,
        public readonly ServiceRequest $serviceRequest,
    ) {
    }

    public static function success(ServiceRequest $sr): self
    {
        return new self(self::STATUS_SUCCESS, $sr);
    }

    public static function alreadyRefused(ServiceRequest $sr): self
    {
        return new self(self::STATUS_ALREADY_REFUSED, $sr);
    }

    public static function notEligible(ServiceRequest $sr): self
    {
        return new self(self::STATUS_NOT_ELIGIBLE, $sr);
    }

    public function isSuccess(): bool
    {
        return $this->status !== self::STATUS_NOT_ELIGIBLE;
    }

    public function getMessage(): string
    {
        return __('provider.refusal.' . $this->status);
    }

    public function toArray(): array
    {
        return [
            'status'      => $this->status,
            'success'     => $this->isSuccess(),
            'message'     => $this->getMessage(),
            'service_uid' => $this->serviceRequest->service_uid,
        ];
    }
}
