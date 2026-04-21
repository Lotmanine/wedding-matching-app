<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_uid',
        'wed_request_id',
        'service_type',
        'status',
        'current_round',
        'round_started_at',
        'round_1_provider_ids',
        'round_2_provider_ids',
        'round_3_provider_ids',
        'all_notified_provider_ids',
        'refused_by_provider_ids',
        'unlocked_by_user_id',
        'unlocked_at',
        'unlocked_via',
        'credits_charged',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'round_1_provider_ids'      => 'array',
            'round_2_provider_ids'      => 'array',
            'round_3_provider_ids'      => 'array',
            'all_notified_provider_ids' => 'array',
            'refused_by_provider_ids'   => 'array',
            'current_round'             => 'integer',
            'credits_charged'           => 'integer',
            'service_type'              => ServiceType::class,
            'status'                    => RequestStatus::class,
            'round_started_at'          => 'datetime',
            'unlocked_at'               => 'datetime',
            'expires_at'                => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceRequest $sr): void {
            if (empty($sr->service_uid)) {
                $sr->service_uid = self::generateUid();
            }
            if (empty($sr->expires_at) && $sr->wed_request_id) {
                $wr = WedRequest::find($sr->wed_request_id);
                if ($wr) {
                    $sr->expires_at = $wr->expires_at;
                }
            }
            if (empty($sr->expires_at)) {
                $sr->expires_at = now()->addDays(14);
            }
        });
    }

    protected static function generateUid(): string
    {
        $date = now()->format('Ymd');
        for ($i = 0; $i < 10; $i++) {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $uid = "SRV-{$date}-{$suffix}";
            if (! self::withTrashed()->where('service_uid', $uid)->exists()) {
                return $uid;
            }
        }
        throw new \RuntimeException('Unable to generate unique service_uid after 10 attempts');
    }

    public function wedRequest(): BelongsTo
    {
        return $this->belongsTo(WedRequest::class);
    }

    public function unlockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by_user_id');
    }

    public function scopePendingMatch(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RequestStatus::New->value,
            RequestStatus::Matching->value,
            RequestStatus::Round1->value,
            RequestStatus::Round2->value,
            RequestStatus::Round3->value,
            RequestStatus::OpenMarketplace->value,
        ])->where('expires_at', '>', now());
    }

    public function scopeRoundTimedOut(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RequestStatus::Round1->value,
            RequestStatus::Round2->value,
            RequestStatus::Round3->value,
        ])->whereNotNull('round_started_at')
          ->where('round_started_at', '<=', now()->subHours(2));
    }

    public function isLocked(): bool
    {
        return $this->unlocked_by_user_id !== null;
    }
}
