<?php

declare(strict_types=1);

namespace App\Services\Matching;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class LeadEligibilityService
{
    public function eligibleLeadsQuery(User $provider): Builder
    {
        if (! $this->providerCanReceiveLeads($provider)) {
            return ServiceRequest::query()->whereRaw('1 = 0');
        }

        $provider->loadMissing(['providerServices', 'providerCoverage']);

        return ServiceRequest::query()
            ->eligibleForProvider($provider)
            ->with(['wedRequest']);
    }

    public function canViewLead(User $provider, ServiceRequest $sr): bool
    {
        if (! $this->providerCanReceiveLeads($provider)) {
            return false;
        }

        $provider->loadMissing(['providerServices', 'providerCoverage']);

        return ServiceRequest::query()
            ->whereKey($sr->id)
            ->eligibleForProvider($provider)
            ->exists();
    }

    public function countEligibleLeads(User $provider): int
    {
        return (int) Cache::remember(
            "leads:count:{$provider->id}",
            now()->addSeconds(60),
            fn () => $this->eligibleLeadsQuery($provider)->count(),
        );
    }

    private function providerCanReceiveLeads(User $provider): bool
    {
        if ($provider->status !== 'active') {
            return false;
        }

        $profile = $provider->providerProfile ?? $provider->loadMissing('providerProfile')->providerProfile;

        return $profile !== null && (bool) $profile->verified;
    }
}
