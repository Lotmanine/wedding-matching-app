<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CreditsGranted;
use App\Events\LeadAccepted;
use App\Events\LeadOpenedToMarketplace;
use App\Events\LeadRefused;
use App\Events\ProviderRegistered;
use App\Events\RoundStarted;
use App\Events\RoundTimedOut;
use App\Events\WedRequestSubmitted;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Events\Dispatcher;

class WebhookEventListener
{
    public function __construct(private readonly WebhookDispatcher $dispatcher)
    {
    }

    public function handleWedRequestSubmitted(WedRequestSubmitted $event): void
    {
        $wr = $event->wedRequest->fresh(['serviceRequests']);

        $this->dispatcher->dispatch('wed_request.submitted', [
            'wed_uid' => $wr->wed_uid,
            'couple' => [
                'first_name'  => $wr->couple_first_name,
                'whatsapp'    => $wr->couple_whatsapp,
                'city_code' => (string) $wr->couple_city_code,
                'district'     => $wr->couple_district,
            ],
            'wedding' => [
                'date'         => $wr->wedding_date?->toDateString(),
                'season'       => $wr->wedding_season,
                'guest_count'  => $wr->guest_count?->value,
                'budget_range' => $wr->budget_range?->value,
            ],
            'services_needed'  => (array) $wr->services_needed,
            'description'      => $wr->description,
            'service_requests' => $wr->serviceRequests->map(fn ($sr) => [
                'service_uid'  => $sr->service_uid,
                'service_type' => $sr->service_type?->value,
                'status'       => $sr->status?->value,
            ])->all(),
            'expires_at' => $wr->expires_at?->toAtomString(),
        ]);
    }

    public function handleRoundStarted(RoundStarted $event): void
    {
        $this->dispatcher->dispatch('service_request.round_started', [
            'service_uid'  => $event->serviceRequest->service_uid,
            'round'        => $event->round,
            'provider_ids' => $event->providerIds,
        ]);
    }

    public function handleRoundTimedOut(RoundTimedOut $event): void
    {
        $this->dispatcher->dispatch('service_request.round_timed_out', [
            'service_uid'    => $event->serviceRequest->service_uid,
            'previous_round' => $event->previousRound,
            'current_status' => $event->serviceRequest->status?->value,
        ]);
    }

    public function handleLeadAccepted(LeadAccepted $event): void
    {
        $this->dispatcher->dispatch('service_request.accepted', [
            'service_uid'   => $event->serviceRequest->service_uid,
            'winner_id'     => $event->winner->id,
            'winner_email'  => $event->winner->email,
            'credits_charged' => (int) $event->serviceRequest->credits_charged,
            'unlocked_via'  => $event->serviceRequest->unlocked_via,
        ]);
    }

    public function handleLeadRefused(LeadRefused $event): void
    {
        $this->dispatcher->dispatch('service_request.refused', [
            'service_uid'   => $event->serviceRequest->service_uid,
            'refuser_id'    => $event->refuser->id,
        ]);
    }

    public function handleLeadOpenedToMarketplace(LeadOpenedToMarketplace $event): void
    {
        $this->dispatcher->dispatch('service_request.opened_to_marketplace', [
            'service_uid' => $event->serviceRequest->service_uid,
        ]);
    }

    public function handleProviderRegistered(ProviderRegistered $event): void
    {
        $this->dispatcher->dispatch('provider.registered', [
            'user_id'       => $event->user->id,
            'business_name' => $event->profile->business_name,
            'email'         => $event->user->email,
            'city_code'   => (string) $event->profile->primary_city_code,
        ]);
    }

    public function handleCreditsGranted(CreditsGranted $event): void
    {
        // Idempotency: never re-dispatch a `purchase.completed` for a purchase whose receipt
        // has already been emailed. Defends against re-firing of CreditsGranted on retries.
        // The public demo sends no receipts: its addresses are not real.
        $purchase = $event->purchase->fresh(['user.providerProfile', 'unlockPack']);
        if ($purchase?->receipt_emailed_at !== null || config('wedding.demo.enabled')) {
            return;
        }

        $user = $purchase?->user ?? $event->user;
        $profile = $user?->providerProfile;

        $langs = (array) ($profile?->languages ?? []);
        $preferredLanguage = in_array('fr', $langs, true) && ! in_array('en', $langs, true) ? 'fr' : 'en';

        $receiptUrl = url("/api/v1/n8n/receipts/{$event->purchase->purchase_uid}.pdf");

        $this->dispatcher->dispatch('purchase.completed', [
            'purchase_uid'       => $event->purchase->purchase_uid,
            'user_id'            => $user?->id,
            'provider_id'        => $user?->id,
            'provider_email'     => $user?->email,
            'provider_name'      => $profile?->business_name ?? $user?->name,
            'preferred_language' => $preferredLanguage,
            'pack_name'          => $purchase?->unlockPack?->name,
            'amount'         => (int) $event->purchase->amount,
            'currency'       => (string) $event->purchase->currency,
            'credits_granted'    => (int) $event->purchase->unlocks_count,
            'unlocks'            => (int) $event->purchase->unlocks_count, // backwards-compat for any older subscriber
            'completed_at'       => ($purchase?->credits_granted_at ?? $purchase?->paid_at ?? now())->toAtomString(),
            'receipt_pdf_url'    => $receiptUrl,
        ]);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            WedRequestSubmitted::class        => 'handleWedRequestSubmitted',
            RoundStarted::class               => 'handleRoundStarted',
            RoundTimedOut::class              => 'handleRoundTimedOut',
            LeadAccepted::class               => 'handleLeadAccepted',
            LeadRefused::class                => 'handleLeadRefused',
            LeadOpenedToMarketplace::class    => 'handleLeadOpenedToMarketplace',
            ProviderRegistered::class         => 'handleProviderRegistered',
            CreditsGranted::class             => 'handleCreditsGranted',
        ];
    }
}
