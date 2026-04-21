<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Events\LeadAccepted;
use App\Events\LeadRefused;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\Credits\CreditsService;
use App\Services\Matching\Results\AcceptResult;
use App\Services\Matching\Results\RefuseResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeadAcceptanceService
{
    public function __construct(
        private readonly CreditsService $credits,
        private readonly LeadEligibilityService $eligibility,
        private readonly RoundStateMachine $stateMachine,
    ) {
    }

    public function accept(User $provider, ServiceRequest $sr, string $via = 'dashboard'): AcceptResult
    {
        return DB::transaction(function () use ($provider, $sr, $via) {
            $locked = ServiceRequest::where('id', $sr->id)->lockForUpdate()->first();

            if ($locked === null) {
                return AcceptResult::expired($sr);
            }

            if ($locked->unlocked_by_user_id !== null) {
                return AcceptResult::alreadyTaken($locked);
            }

            $statusValue = $locked->status?->value ?? (string) $locked->status;
            if (
                $locked->expires_at !== null && $locked->expires_at->isPast()
                || in_array($statusValue, ['expired', 'deleted'], true)
            ) {
                return AcceptResult::expired($locked);
            }

            if (! $this->eligibility->canViewLead($provider, $locked)) {
                return AcceptResult::notEligible($locked);
            }

            $cost = (int) config('zawaj.credits.unlock_cost', 1);
            $available = (int) ($provider->providerProfile->credit_balance ?? 0);
            if ($available < $cost) {
                return AcceptResult::insufficientCredits($locked, $cost, $available);
            }

            $this->credits->deduct(
                user: $provider,
                amount: $cost,
                type: 'unlock',
                reference: $locked,
                notes: "Unlock {$locked->service_uid}",
            );

            $this->stateMachine->markAccepted($locked, $provider, $via);
            $locked->credits_charged = $cost;
            $locked->save();

            $profile = $provider->providerProfile;
            $profile->increment('leads_accepted');
            $profile->refresh()->recalculateStats();

            $this->flushEligibilityCaches($locked);

            LeadAccepted::dispatch($locked, $provider);

            return AcceptResult::success($locked, $provider);
        });
    }

    public function refuse(User $provider, ServiceRequest $sr): RefuseResult
    {
        return DB::transaction(function () use ($provider, $sr) {
            $locked = ServiceRequest::where('id', $sr->id)->lockForUpdate()->first();

            if ($locked === null) {
                return RefuseResult::notEligible($sr);
            }

            if ($locked->unlocked_by_user_id !== null) {
                return RefuseResult::notEligible($locked);
            }

            $refused = (array) ($locked->refused_by_provider_ids ?? []);
            if (in_array($provider->id, $refused, true)) {
                return RefuseResult::alreadyRefused($locked);
            }

            $refused[] = $provider->id;
            $locked->refused_by_provider_ids = array_values(array_unique($refused));
            $locked->save();

            if ($locked->wasChanged('refused_by_provider_ids')) {
                $profile = $provider->providerProfile;
                if ($profile) {
                    $profile->increment('leads_refused');
                    $profile->refresh()->recalculateStats();
                }
            }

            Cache::forget("leads:count:{$provider->id}");

            LeadRefused::dispatch($locked, $provider);

            return RefuseResult::success($locked);
        });
    }

    private function flushEligibilityCaches(ServiceRequest $sr): void
    {
        $allIds = collect([])
            ->merge((array) ($sr->all_notified_provider_ids ?? []))
            ->merge((array) ($sr->round_1_provider_ids ?? []))
            ->merge((array) ($sr->round_2_provider_ids ?? []))
            ->merge((array) ($sr->round_3_provider_ids ?? []))
            ->unique()
            ->values();

        foreach ($allIds as $userId) {
            Cache::forget("leads:count:{$userId}");
        }

        Cache::forget('leads:open_marketplace:version');
    }
}
