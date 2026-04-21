<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEndpoint extends Model
{
    protected $fillable = ['name', 'url', 'secret', 'events', 'active'];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'active' => 'boolean',
        ];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function subscribesTo(string $eventType): bool
    {
        return in_array($eventType, (array) ($this->events ?? []), true);
    }
}
