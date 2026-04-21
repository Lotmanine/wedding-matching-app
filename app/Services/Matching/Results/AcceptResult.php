<?php

declare(strict_types=1);

namespace App\Services\Matching\Results;

use App\Models\ServiceRequest;
use App\Models\User;

final class AcceptResult
{
    public const STATUS_SUCCESS              = 'success';
    public const STATUS_ALREADY_TAKEN        = 'already_taken';
    public const STATUS_EXPIRED              = 'expired';
    public const STATUS_NOT_ELIGIBLE         = 'not_eligible';
    public const STATUS_INSUFFICIENT_CREDITS = 'insufficient_credits';

    private function __construct(
        public readonly string $status,
        public readonly ServiceRequest $serviceRequest,
        public readonly ?User $winner = null,
        public readonly ?int $cost = null,
        public readonly ?int $available = null,
    ) {
    }

    public static function success(ServiceRequest $sr, User $winner): self
    {
        return new self(self::STATUS_SUCCESS, $sr, $winner);
    }

    public static function alreadyTaken(ServiceRequest $sr): self
    {
        return new self(self::STATUS_ALREADY_TAKEN, $sr);
    }

    public static function expired(ServiceRequest $sr): self
    {
        return new self(self::STATUS_EXPIRED, $sr);
    }

    public static function notEligible(ServiceRequest $sr): self
    {
        return new self(self::STATUS_NOT_ELIGIBLE, $sr);
    }

    public static function insufficientCredits(ServiceRequest $sr, int $cost, int $available): self
    {
        return new self(self::STATUS_INSUFFICIENT_CREDITS, $sr, cost: $cost, available: $available);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function getMessage(): string
    {
        return __('provider.acceptance.' . $this->status, [
            'cost'      => $this->cost ?? 0,
            'available' => $this->available ?? 0,
        ]);
    }

    public function toArray(): array
    {
        return [
            'status'       => $this->status,
            'success'      => $this->isSuccess(),
            'message'      => $this->getMessage(),
            'service_uid'  => $this->serviceRequest->service_uid,
            'cost'         => $this->cost,
            'available'    => $this->available,
        ];
    }
}
