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
                $sr->expires_at = now()->addDays((int) config('zawaj.anpdp.retention_days', 14));
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

    /**
     * ANPDP display anonymization: the couple's contact details stop being
     * shown to the provider N days after acceptance (retention_days, 14 by
     * default). This masks the UI only — actual data erasure stays with the
     * scheduled zawaj:cleanup-* commands.
     */
    public function anonymizeAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->unlocked_at === null) {
            return null;
        }

        return $this->unlocked_at->copy()->addDays((int) config('zawaj.anpdp.retention_days', 14));
    }

    public function isAnonymized(): bool
    {
        $at = $this->anonymizeAt();

        return $at !== null && $at->isPast();
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

    public function scopeEligibleForProvider(Builder $query, User $provider): Builder
    {
        $serviceTypes = $provider->providerServices->pluck('service_type')
            ->map(fn ($v) => $v instanceof \App\Enums\ServiceType ? $v->value : (string) $v)
            ->values()
            ->all();

        $coverage = $provider->providerCoverage->pluck('wilaya_code')
            ->map(fn ($v) => is_object($v) && property_exists($v, 'value') ? $v->value : (string) $v)
            ->values()
            ->all();

        if (empty($serviceTypes) || empty($coverage)) {
            return $query->whereRaw('1 = 0');
        }

        $providerId = $provider->id;

        return $query
            ->whereIn('status', [
                RequestStatus::Round1->value,
                RequestStatus::Round2->value,
                RequestStatus::Round3->value,
                RequestStatus::OpenMarketplace->value,
            ])
            ->where('expires_at', '>', now())
            ->whereNull('unlocked_by_user_id')
            ->whereIn('service_type', $serviceTypes)
            ->where(function (Builder $q) use ($providerId) {
                $q->whereNull('refused_by_provider_ids')
                  ->orWhereJsonDoesntContain('refused_by_provider_ids', $providerId);
            })
            // Wilaya coverage is required for automatic/marketplace visibility,
            // BUT a provider explicitly placed in a round (e.g. an admin manual
            // assignment overriding geography) always sees that lead.
            ->where(function (Builder $q) use ($coverage, $providerId) {
                $q->whereHas('wedRequest', function (Builder $q2) use ($coverage) {
                    $q2->whereIn('couple_wilaya_code', $coverage);
                })
                    ->orWhereJsonContains('round_1_provider_ids', $providerId)
                    ->orWhereJsonContains('round_2_provider_ids', $providerId)
                    ->orWhereJsonContains('round_3_provider_ids', $providerId);
            })
            ->where(function (Builder $q) use ($providerId) {
                $q->where(function (Builder $q2) use ($providerId) {
                    $q2->where('status', RequestStatus::Round1->value)
                        ->whereJsonContains('round_1_provider_ids', $providerId);
                })->orWhere(function (Builder $q2) use ($providerId) {
                    $q2->where('status', RequestStatus::Round2->value)
                        ->where(function (Builder $q3) use ($providerId) {
                            $q3->whereJsonContains('round_1_provider_ids', $providerId)
                               ->orWhereJsonContains('round_2_provider_ids', $providerId);
                        });
                })->orWhere(function (Builder $q2) use ($providerId) {
                    $q2->where('status', RequestStatus::Round3->value)
                        ->where(function (Builder $q3) use ($providerId) {
                            $q3->whereJsonContains('round_1_provider_ids', $providerId)
                               ->orWhereJsonContains('round_2_provider_ids', $providerId)
                               ->orWhereJsonContains('round_3_provider_ids', $providerId);
                        });
                })->orWhere('status', RequestStatus::OpenMarketplace->value);
            });
    }

    public function scopeRoundTimedOut(Builder $query): Builder
    {
        return $query->whereIn('status', [
            RequestStatus::Round1->value,
            RequestStatus::Round2->value,
            RequestStatus::Round3->value,
        ])->whereNotNull('round_started_at')
          ->where('round_started_at', '<=', now()->subMinutes(\App\Services\Matching\RoundStateMachine::roundTimeoutMinutes()));
    }

    public function isLocked(): bool
    {
        return $this->unlocked_by_user_id !== null;
    }
}
