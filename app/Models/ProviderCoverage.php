<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderCoverage extends Model
{
    protected $table = 'provider_coverage';

    protected $fillable = [
        'user_id',
        'wilaya_code',
        'travel_fee_dzd',
    ];

    protected function casts(): array
    {
        return [
            'travel_fee_dzd' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
