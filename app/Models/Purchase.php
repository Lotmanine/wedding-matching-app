<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_uid',
        'user_id',
        'unlock_pack_id',
        'amount_dzd',
        'unlocks_count',
        'status',
        'gateway',
        'payment_method',
        'payment_reference',
        'proof_path',
        'gateway_checkout_id',
        'gateway_payment_id',
        'gateway_metadata',
        'paid_at',
        'failed_at',
        'failure_reason',
        'credits_granted_at',
        'credit_ledger_id',
        'receipt_emailed_at',
        'smtp_message_id',
    ];

    protected function casts(): array
    {
        return [
            'gateway_metadata'    => 'array',
            'paid_at'             => 'datetime',
            'failed_at'           => 'datetime',
            'credits_granted_at'  => 'datetime',
            'receipt_emailed_at'  => 'datetime',
            'amount_dzd'          => 'integer',
            'unlocks_count'       => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Purchase $p): void {
            if (empty($p->purchase_uid)) {
                $p->purchase_uid = self::generateUid();
            }
        });
    }

    protected static function generateUid(): string
    {
        $date = now()->format('Ymd');
        for ($i = 0; $i < 10; $i++) {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $uid = "PUR-{$date}-{$suffix}";
            if (! self::where('purchase_uid', $uid)->exists()) {
                return $uid;
            }
        }
        throw new \RuntimeException('Unable to generate unique purchase_uid after 10 attempts');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unlockPack(): BelongsTo
    {
        return $this->belongsTo(UnlockPack::class);
    }

    public function creditLedger(): BelongsTo
    {
        return $this->belongsTo(CreditLedger::class, 'credit_ledger_id');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', ['failed', 'cancelled']);
    }
}
