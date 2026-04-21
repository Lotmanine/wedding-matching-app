<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commune extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'wilaya_id',
        'name_fr',
        'name_ar',
        'postal_code',
    ];

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function scopeForWilaya(Builder $query, int $wilayaId): Builder
    {
        return $query->where('wilaya_id', $wilayaId);
    }
}
