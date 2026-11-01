<?php

declare(strict_types=1);

namespace App\Listeners\Whatsapp;

use App\Events\LeadAccepted;
use App\Services\Webhooks\WebhookDispatcher;
use App\Support\Whatsapp\RecipientPayloadBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchLeadAcceptedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly WebhookDispatcher $dispatcher)
    {
    }

    public function handle(LeadAccepted $event): void
    {
        $sr = $event->serviceRequest->loadMissing('wedRequest');
        $winner = $event->winner->loadMissing('providerProfile');

        $couple = RecipientPayloadBuilder::couplePayload($sr);
        $service = RecipientPayloadBuilder::servicePayload($sr);
        $winnerLang = $this->pickLanguage($winner->providerProfile?->languages ?? []);

        $payload = [
            'service_request_id' => $sr->service_uid,
            'winner' => [
                'provider_id'        => $winner->id,
                'business_name'      => (string) ($winner->providerProfile?->business_name ?? $winner->name),
                'phone_e164'         => RecipientPayloadBuilder::normalizeE164($winner->whatsapp ?: $winner->phone),
                'preferred_language' => $winnerLang,
            ],
            'couple' => [
                'first_name'         => $couple['first_name'],
                'phone_e164'         => $couple['phone_e164'],
                'preferred_language' => $couple['preferred_language'],
            ],
            'service' => [
                'type'       => $service['type'],
                'event_date' => $service['event_date'],
            ],
            'template_intent' => 'winner_confirmation',
        ];

        $this->dispatcher->dispatch('lead.accepted', $payload);
    }

    private function pickLanguage(array $langs): string
    {
        if (in_array('en', $langs, true)) {
            return 'en';
        }
        if (in_array('fr', $langs, true)) {
            return 'fr';
        }

        return $langs[0] ?? 'en';
    }
}
