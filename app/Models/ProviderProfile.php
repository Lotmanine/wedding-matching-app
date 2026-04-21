<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Wilaya;
use Database\Factories\ProviderProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderProfile extends Model
{
    /** @use HasFactory<ProviderProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_description',
        'business_type',
        'trade_register',
        'primary_wilaya_code',
        'price_tier',
        'min_price',
        'max_price',
        'years_experience',
        'portfolio_url',
        'portfolio_images',
        'logo_url',
        'languages',
        'verified',
        'verified_at',
        'rating_avg',
        'rating_count',
        'leads_received',
        'leads_accepted',
        'leads_refused',
        'acceptance_rate',
        'credit_balance',
        'onboarding_completed',
    ];

    protected function casts(): array
    {
        return [
            'portfolio_images'     => 'array',
            'languages'            => 'array',
            'verified'             => 'boolean',
            'onboarding_completed' => 'boolean',
            'primary_wilaya_code'  => Wilaya::class,
            'verified_at'          => 'datetime',
            'rating_avg'           => 'decimal:2',
            'acceptance_rate'      => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditLedger(): HasMany
    {
        return $this->hasMany(CreditLedger::class, 'user_id', 'user_id');
    }

    public function recalculateStats(): void
    {
        $received = (int) $this->leads_received;
        $this->acceptance_rate = $received > 0
            ? round(((int) $this->leads_accepted / $received) * 100, 2)
            : 0;
        $this->save();
    }
}
