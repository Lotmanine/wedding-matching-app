<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'webhook_endpoint_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'attempts',
        'delivered_at',
        'failed_at',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'        => 'array',
            'delivered_at'   => 'datetime',
            'failed_at'      => 'datetime',
            'next_retry_at'  => 'datetime',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class, 'webhook_endpoint_id');
    }
}
