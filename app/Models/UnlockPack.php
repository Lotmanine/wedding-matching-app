<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UnlockPackFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnlockPack extends Model
{
    /** @use HasFactory<UnlockPackFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'unlocks_count',
        'price_dzd',
        'is_popular',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'is_popular'    => 'boolean',
            'active'        => 'boolean',
            'unlocks_count' => 'integer',
            'price_dzd'     => 'integer',
            'sort_order'    => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('sort_order');
    }
}
