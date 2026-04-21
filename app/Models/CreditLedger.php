<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CreditLedgerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditLedger extends Model
{
    /** @use HasFactory<CreditLedgerFactory> */
    use HasFactory;

    protected $table = 'credit_ledger';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'delta',
        'balance_after',
        'type',
        'reference_type',
        'reference_id',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'delta'         => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
