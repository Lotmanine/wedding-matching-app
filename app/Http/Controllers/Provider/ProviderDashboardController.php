<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Matching\LeadEligibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderDashboardController extends Controller
{
    public function __construct(private readonly LeadEligibilityService $eligibility)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user()->load(['providerProfile', 'providerServices', 'providerCoverage']);

        $eligibleCount = $this->eligibility->countEligibleLeads($user);
        $recentLeads = $this->eligibility
            ->eligibleLeadsQuery($user)
            ->orderByDesc('round_started_at')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('provider.dashboard', [
            'user'          => $user,
            'profile'       => $user->providerProfile,
            'eligibleCount' => $eligibleCount,
            'recentLeads'   => $recentLeads,
        ]);
    }
}
