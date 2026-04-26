<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Whatsapp;

use Illuminate\Foundation\Http\FormRequest;

class InboundButtonTapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wa_message_id'  => ['required', 'string', 'max:128'],
            'from_phone'     => ['required', 'string', 'regex:/^\+\d{8,15}$/'],
            'button_payload' => ['required', 'string', 'max:512'],
            'button_text'    => ['nullable', 'string', 'max:200'],
            'in_reply_to'    => ['nullable', 'string', 'max:128'],
            'timestamp'      => ['required', 'string', 'max:32'],
        ];
    }
}
