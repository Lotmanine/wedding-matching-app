<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
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
        'primary_city_code',
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
        'admin_notes',
        'suspended_at',
        'suspended_reason',
    ];

    protected function casts(): array
    {
        return [
            'portfolio_images'     => 'array',
            'languages'            => 'array',
            'verified'             => 'boolean',
            'onboarding_completed' => 'boolean',
            'verified_at'          => 'datetime',
            'suspended_at'         => 'datetime',
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

    /** The currency of this provider's packs and travel fees, from their primary city. */
    public function currency(): string
    {
        return Money::currencyForCity($this->primary_city_code);
    }

    public function recalculateStats(): void
    {
        $received = (int) $this->leads_received;
        $this->acceptance_rate = $received > 0
            ? round(((int) $this->leads_accepted / $received) * 100, 2)
            : 0;
        $this->save();
    }

    /**
     * Whether the account is currently suspended by an admin.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * A provider is "active" — eligible to receive/accept leads and spend
     * credits — only when a super-admin has verified them and they are not
     * suspended. This is the single gate every credit/lead action checks.
     */
    public function isActive(): bool
    {
        return (bool) $this->verified && ! $this->isSuspended();
    }

    /**
     * Whether the provider may buy credit packs. Unverified or suspended
     * providers cannot: they could otherwise accumulate credits they are
     * not allowed to spend. Kept identical to isActive() so the buy gate
     * and the lead gate never drift apart.
     */
    public function canPurchaseCredits(): bool
    {
        return $this->isActive();
    }
}
