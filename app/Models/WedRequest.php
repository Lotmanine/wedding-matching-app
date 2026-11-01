<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use App\Support\Money;
use Database\Factories\WedRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WedRequest extends Model
{
    public const SEASONS = ['spring', 'summer', 'autumn', 'winter'];

    /** @use HasFactory<WedRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wed_uid',
        'status',
        'couple_first_name',
        'couple_whatsapp',
        'couple_city_code',
        'couple_district',
        'wedding_date',
        'wedding_season',
        'guest_count',
        'budget_range',
        'description',
        'services_needed',
        'budget_warning_shown',
        'source',
        'ip_address',
        'user_agent',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'services_needed'      => 'array',
            'wedding_date'         => 'date',
            'expires_at'           => 'datetime',
            'budget_warning_shown' => 'boolean',
            'status'               => RequestStatus::class,
            'guest_count'          => GuestCount::class,
            'budget_range'         => BudgetRange::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WedRequest $wr): void {
            if (empty($wr->wed_uid)) {
                $wr->wed_uid = self::generateUid();
            }
            if (empty($wr->expires_at)) {
                $wr->expires_at = now()->addDays((int) config('wedding.privacy.retention_days', 14));
            }
        });

        static::deleted(function (WedRequest $wr): void {
            if ($wr->isForceDeleting()) {
                return;
            }
            $wr->serviceRequests()->each(fn ($sr) => $sr->delete());
        });
    }

    protected static function generateUid(): string
    {
        $date = now()->format('Ymd');
        for ($i = 0; $i < 10; $i++) {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $uid = "WED-{$date}-{$suffix}";
            if (! self::withTrashed()->where('wed_uid', $uid)->exists()) {
                return $uid;
            }
        }
        throw new \RuntimeException('Unable to generate unique wed_uid after 10 attempts');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            RequestStatus::Expired->value,
            RequestStatus::Deleted->value,
        ])->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /** The currency of this wedding's amounts (budget), from its city. */
    public function currency(): string
    {
        return Money::currencyForCity($this->couple_city_code);
    }

    /** The season in the reader's language. */
    public function seasonLabel(): ?string
    {
        return self::labelForSeason($this->wedding_season);
    }

    public static function labelForSeason(?string $season): ?string
    {
        return match ($season) {
            'spring' => __('Printemps'),
            'summer' => __('Été'),
            'autumn' => __('Automne'),
            'winter' => __('Hiver'),
            default  => null,
        };
    }
}
