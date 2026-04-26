<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Whatsapp;

use Illuminate\Foundation\Http\FormRequest;

class InboundStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wa_message_id' => ['required', 'string', 'max:128'],
            'status'        => ['required', 'in:sent,delivered,read,failed'],
            'recipient_id'  => ['nullable', 'string', 'regex:/^\+\d{8,15}$/'],
            'timestamp'     => ['required', 'string', 'max:32'],
            'error'         => ['nullable', 'string', 'max:500'],
        ];
    }
}
