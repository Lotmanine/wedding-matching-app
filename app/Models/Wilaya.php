<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilaya extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'code',
        'name_fr',
        'name_ar',
        'region',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('id');
    }

    public function label(): string
    {
        return $this->name_fr;
    }
}
