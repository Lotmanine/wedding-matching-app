<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use Database\Factories\WedRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WedRequest extends Model
{
    /** @use HasFactory<WedRequestFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wed_uid',
        'status',
        'couple_first_name',
        'couple_whatsapp',
        'couple_wilaya_code',
        'couple_commune',
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
                $wr->expires_at = now()->addDays((int) config('zawaj.anpdp.retention_days', 14));
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

    /**
     * The commune as shown to the reader. It is stored under its French name;
     * Arabic pages show the Arabic name of the same commune.
     */
    public function communeLabel(): ?string
    {
        if (! $this->couple_commune || app()->getLocale() !== 'ar') {
            return $this->couple_commune;
        }

        $communes = Commune::query()->where('name_fr', $this->couple_commune);

        $nameAr = (clone $communes)
            ->whereHas('wilaya', fn (Builder $q) => $q->where('code', (string) $this->couple_wilaya_code))
            ->value('name_ar') ?: $communes->value('name_ar');

        return $nameAr ?: $this->couple_commune;
    }

    /** The season (stored in French: Printemps, Été…) in the reader's language. */
    public function seasonLabel(): ?string
    {
        return $this->wedding_season ? __($this->wedding_season) : null;
    }
}
