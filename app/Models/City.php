<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'code',
        'country',
        'name',
        'region',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('id');
    }

    public function label(): string
    {
        return $this->name;
    }
}
