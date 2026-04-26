<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WhatsappMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMessage>
 */
class WhatsappMessageFactory extends Factory
{
    protected $model = WhatsappMessage::class;

    public function definition(): array
    {
        return [
            'service_request_uid'  => 'SRV-' . now()->format('Ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'provider_id'          => null,
            'recipient_phone_e164' => '+213' . $this->faker->numerify('#########'),
            'direction'            => 'outbound',
            'event_type'           => 'round.started',
            'template_intent'      => 'round_teaser',
            'role'                 => 'provider',
            'wa_message_id'        => 'wamid.' . $this->faker->bothify('?????????????????????'),
            'status'               => 'sent',
            'sent_at'              => now(),
        ];
    }
}
