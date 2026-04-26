<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WhatsappMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    /** @use HasFactory<WhatsappMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'service_request_uid',
        'provider_id',
        'recipient_phone_e164',
        'direction',
        'event_type',
        'template_intent',
        'role',
        'wa_message_id',
        'status',
        'error',
        'payload_snapshot',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_snapshot' => 'array',
            'sent_at'          => 'datetime',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_uid', 'service_uid');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
