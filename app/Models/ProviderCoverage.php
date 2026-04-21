<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Wilaya;
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
            'wilaya_code'    => Wilaya::class,
            'travel_fee_dzd' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
