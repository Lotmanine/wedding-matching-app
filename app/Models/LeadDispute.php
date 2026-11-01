<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadDispute extends Model
{
    public const REASONS = ['wrong_number', 'no_response', 'fake', 'other'];

    protected $fillable = [
        'service_request_id',
        'provider_user_id',
        'reason',
        'details',
        'status',
        'admin_note',
        'decided_by_user_id',
        'decided_at',
        'credit_ledger_id',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_user_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function reasonLabel(): string
    {
        return match ($this->reason) {
            'wrong_number' => __('Wrong or unreachable number'),
            'no_response'  => __('No reply from the couple'),
            'fake'         => __('Fake request or spam'),
            default        => __('Other'),
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
