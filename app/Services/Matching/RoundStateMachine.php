<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Enums\RequestStatus;
use App\Events\LeadOpenedToMarketplace;
use App\Events\RoundStarted;
use App\Models\ServiceRequest;
use App\Models\User;

class RoundStateMachine
{
    public const ROUND_TIMEOUT_MINUTES = 120;

    /**
     * Start a specific round with the given provider IDs.
     * @param array<int> $providerIds
     */
    public function startRound(ServiceRequest $sr, int $round, array $providerIds): ServiceRequest
    {
        if ($round < 1 || $round > 3) {
            throw new \InvalidArgumentException("Invalid round: {$round}");
        }

        $status = match ($round) {
            1 => RequestStatus::Round1->value,
            2 => RequestStatus::Round2->value,
            3 => RequestStatus::Round3->value,
        };

        $existing = (array) ($sr->all_notified_provider_ids ?? []);
        $merged = array_values(array_unique(array_merge($existing, $providerIds)));

        $sr->status = $status;
        $sr->current_round = $round;
        $sr->round_started_at = now();
        $sr->setAttribute("round_{$round}_provider_ids", array_values($providerIds));
        $sr->all_notified_provider_ids = $merged;
        $sr->save();

        RoundStarted::dispatch($sr, $round, array_values($providerIds));

        return $sr;
    }

    /**
     * Manually assign a single provider to a service request WITHOUT firing the
     * RoundStarted webhook — for admin-driven ("manual round-robin") matching
     * where the admin notifies the provider themselves. The provider is recorded
     * on the current (or given) round exactly as an automated round would, so
     * they become eligible to see and accept the lead through the normal flow.
     */
    public function assignProviderSilently(ServiceRequest $sr, int $providerId, ?int $round = null): ServiceRequest
    {
        $round = $round ?? ($sr->current_round >= 1 && $sr->current_round <= 3 ? $sr->current_round : 1);
        $round = max(1, min(3, $round));

        $status = match ($round) {
            1 => RequestStatus::Round1->value,
            2 => RequestStatus::Round2->value,
            3 => RequestStatus::Round3->value,
        };

        $roundIds = (array) ($sr->getAttribute("round_{$round}_provider_ids") ?? []);
        $roundIds = array_values(array_unique([...$roundIds, $providerId]));

        $allIds = array_values(array_unique([
            ...((array) ($sr->all_notified_provider_ids ?? [])),
            $providerId,
        ]));

        $sr->status = $status;
        $sr->current_round = $round;
        $sr->round_started_at = now();
        // Ensure the lead is live: give it a fresh timeout window if it has no
        // future expiry, otherwise a manually-assigned (often older) request
        // would already be past its expires_at and stay invisible.
        if ($sr->expires_at === null || $sr->expires_at->isPast()) {
            $sr->expires_at = now()->addMinutes(self::ROUND_TIMEOUT_MINUTES);
        }
        $sr->setAttribute("round_{$round}_provider_ids", $roundIds);
        $sr->all_notified_provider_ids = $allIds;
        $sr->save();

        // Intentionally NO RoundStarted event — the admin notifies the provider.

        return $sr;
    }

    /**
     * Advance the current round. If round 3 is timing out / finishing, open to marketplace.
     * Otherwise, move into "matching" state so n8n can pick the next round's providers.
     */
    public function advanceRound(ServiceRequest $sr): ServiceRequest
    {
        if ($sr->current_round >= 3) {
            return $this->openToMarketplace($sr);
        }

        $sr->status = RequestStatus::Matching->value;
        $sr->round_started_at = null;
        $sr->save();

        return $sr;
    }

    public function openToMarketplace(ServiceRequest $sr): ServiceRequest
    {
        $sr->status = RequestStatus::OpenMarketplace->value;
        $sr->current_round = 4;
        $sr->round_started_at = null;
        $sr->save();

        LeadOpenedToMarketplace::dispatch($sr);

        return $sr;
    }

    public function markAccepted(ServiceRequest $sr, User $winner, string $via = 'dashboard'): ServiceRequest
    {
        $sr->status = RequestStatus::Accepted->value;
        $sr->unlocked_by_user_id = $winner->id;
        $sr->unlocked_at = now();
        $sr->unlocked_via = $via;
        $sr->save();

        return $sr;
    }

    /**
     * Get the IDs of providers who were notified in the current round.
     * @return array<int>
     */
    public function getCurrentRoundProviderIds(ServiceRequest $sr): array
    {
        $round = $sr->current_round;
        if ($round < 1 || $round > 3) {
            return [];
        }

        return (array) $sr->getAttribute("round_{$round}_provider_ids") ?? [];
    }

    public function isRoundTimedOut(ServiceRequest $sr): bool
    {
        if (! in_array($sr->status?->value, [
            RequestStatus::Round1->value,
            RequestStatus::Round2->value,
            RequestStatus::Round3->value,
        ], true)) {
            return false;
        }

        if ($sr->round_started_at === null) {
            return false;
        }

        return $sr->round_started_at->copy()->addMinutes(self::ROUND_TIMEOUT_MINUTES)->isPast();
    }
}
