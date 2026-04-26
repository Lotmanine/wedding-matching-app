<?php

declare(strict_types=1);

namespace App\Listeners\Whatsapp;

use App\Events\RoundStarted;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Webhooks\WebhookDispatcher;
use App\Support\Whatsapp\RecipientPayloadBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchRoundStartedWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly WebhookDispatcher $dispatcher)
    {
    }

    public function handle(RoundStarted $event): void
    {
        $sr = ServiceRequest::with(['wedRequest'])->find($event->serviceRequest->id);
        if (! $sr) {
            return;
        }

        $providers = User::with(['providerProfile'])
            ->whereIn('id', $event->providerIds)
            ->where('status', 'active')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at'))
            ->get();

        if ($providers->isEmpty()) {
            return;
        }

        $expiresAt = $sr->round_started_at
            ? $sr->round_started_at->copy()->addMinutes((int) config('zawaj.matching.round_timeout_minutes', 120))
            : now()->addHours(2);

        $payload = [
            'service_request_id' => $sr->service_uid,
            'round_number'       => $event->round,
            'expires_at'         => $expiresAt->toAtomString(),
            'couple'             => [
                'first_name' => RecipientPayloadBuilder::couplePayload($sr)['first_name'],
                'city'       => RecipientPayloadBuilder::couplePayload($sr)['city'],
            ],
            'service'            => RecipientPayloadBuilder::servicePayload($sr),
            'template_intent'    => 'round_teaser',
            'recipients'         => RecipientPayloadBuilder::forProviders($sr, $providers),
        ];

        $this->dispatcher->dispatch('round.started', $payload);
    }
}
