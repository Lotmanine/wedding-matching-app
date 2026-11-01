<?php

declare(strict_types=1);

namespace App\Listeners\Whatsapp;

use App\Events\LeadOpenedToMarketplace;
use App\Models\User;
use App\Services\Webhooks\WebhookDispatcher;
use App\Support\Whatsapp\RecipientPayloadBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DispatchLeadOpenedToMarketplaceWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(private readonly WebhookDispatcher $dispatcher)
    {
    }

    public function handle(LeadOpenedToMarketplace $event): void
    {
        $sr = $event->serviceRequest->loadMissing('wedRequest');
        $wr = $sr->wedRequest;
        if (! $wr) {
            return;
        }

        $excludeIds = array_unique(array_merge(
            (array) ($sr->all_notified_provider_ids ?? []),
            (array) ($sr->refused_by_provider_ids ?? []),
            $sr->unlocked_by_user_id ? [$sr->unlocked_by_user_id] : [],
        ));

        $serviceTypeValue = $sr->service_type?->value ?? (string) $sr->service_type;
        $coupleCity = (string) $wr->couple_city_code;

        $providers = User::with(['providerProfile'])
            ->where('status', 'active')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at'))
            ->whereHas('providerServices', fn ($q) => $q->where('service_type', $serviceTypeValue))
            ->whereHas('providerCoverage', fn ($q) => $q->where('city_code', $coupleCity))
            ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->get();

        if ($providers->isEmpty()) {
            return;
        }

        $couple = RecipientPayloadBuilder::couplePayload($sr);

        $payload = [
            'service_request_id' => $sr->service_uid,
            'couple' => [
                'first_name' => $couple['first_name'],
                'city'       => $couple['city'],
            ],
            'service'         => RecipientPayloadBuilder::servicePayload($sr),
            'template_intent' => 'marketplace_broadcast',
            'recipients'      => RecipientPayloadBuilder::forProviders($sr, $providers),
        ];

        $this->dispatcher->dispatch('lead.opened_to_marketplace', $payload);
    }
}
