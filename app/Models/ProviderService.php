<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderService extends Model
{
    protected $fillable = [
        'user_id',
        'service_type',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
