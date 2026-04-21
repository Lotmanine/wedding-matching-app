<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Enums\BudgetRange;
use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Wilaya;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\LeadEligibilityService;
use App\Services\Matching\Results\AcceptResult;
use App\Services\Matching\Results\RefuseResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProviderLeadsController extends Controller
{
    public function __construct(
        private readonly LeadEligibilityService $eligibility,
        private readonly LeadAcceptanceService $acceptance,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user()->load(['providerProfile', 'providerServices', 'providerCoverage']);

        $profile = $user->providerProfile;
        $canReceive = $profile !== null && $profile->verified && $user->status === 'active';

        $query = $this->eligibility->eligibleLeadsQuery($user);

        $filters = $request->only(['service_type', 'wilaya_code', 'budget_range', 'sort']);

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (! empty($filters['wilaya_code'])) {
            $query->whereHas('wedRequest', fn ($q) => $q->where('couple_wilaya_code', $filters['wilaya_code']));
        }

        if (! empty($filters['budget_range'])) {
            $query->whereHas('wedRequest', fn ($q) => $q->where('budget_range', $filters['budget_range']));
        }

        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'oldest'        => $query->orderBy('created_at'),
            'wedding_date'  => $query->whereHas('wedRequest', fn ($q) => $q->whereNotNull('wedding_date'))
                                      ->orderByDesc(
                                          \App\Models\WedRequest::select('wedding_date')
                                              ->whereColumn('wed_requests.id', 'service_requests.wed_request_id')
                                      ),
            default         => $query->orderByDesc('round_started_at')->orderByDesc('created_at'),
        };

        $leads = $canReceive
            ? $query->paginate(12)->withQueryString()
            : ServiceRequest::query()->whereRaw('1 = 0')->paginate(12);

        $serviceTypeOptions = collect(ServiceType::cases())
            ->filter(fn ($case) => $user->providerServices
                ->pluck('service_type')
                ->map(fn ($v) => $v instanceof ServiceType ? $v->value : (string) $v)
                ->contains($case->value)
            )->values();

        $coverageCodes = $user->providerCoverage->pluck('wilaya_code')
            ->map(fn ($v) => is_object($v) && property_exists($v, 'value') ? $v->value : (string) $v)
            ->values()
            ->all();
        $wilayaOptions = Wilaya::whereIn('code', $coverageCodes)->orderBy('name_fr')->get();

        $budgetOptions = BudgetRange::cases();

        return view('provider.leads.index', [
            'leads'              => $leads,
            'filters'            => $filters,
            'serviceTypeOptions' => $serviceTypeOptions,
            'wilayaOptions'      => $wilayaOptions,
            'budgetOptions'      => $budgetOptions,
            'canReceive'         => $canReceive,
            'profile'            => $profile,
        ]);
    }

    public function show(Request $request, string $serviceUid)
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)
            ->with(['wedRequest.serviceRequests'])
            ->first();

        if (! $sr) {
            throw new NotFoundHttpException();
        }

        $user = $request->user()->load(['providerProfile', 'providerServices', 'providerCoverage']);

        if ($sr->unlocked_by_user_id === $user->id) {
            return view('provider.leads.accepted', [
                'lead'       => $sr,
                'wedRequest' => $sr->wedRequest,
            ]);
        }

        abort_unless($this->eligibility->canViewLead($user, $sr), 403);

        return view('provider.leads.show', [
            'lead'       => $sr,
            'wedRequest' => $sr->wedRequest,
        ]);
    }

    public function accept(Request $request, string $serviceUid): JsonResponse|RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            throw new NotFoundHttpException();
        }

        $user = $request->user()->load(['providerProfile', 'providerServices', 'providerCoverage']);
        $result = $this->acceptance->accept($user, $sr, 'dashboard');

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result->toArray(), $result->isSuccess() ? 200 : 409);
        }

        return match ($result->status) {
            AcceptResult::STATUS_SUCCESS
                => redirect()
                    ->route('provider.leads.show', $sr->service_uid)
                    ->with('status', $result->getMessage()),
            AcceptResult::STATUS_INSUFFICIENT_CREDITS
                => redirect()
                    ->route('provider.dashboard')
                    ->with('error', $result->getMessage()),
            default
                => redirect()
                    ->route('provider.leads.index')
                    ->with('error', $result->getMessage()),
        };
    }

    public function refuse(Request $request, string $serviceUid): JsonResponse|RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            throw new NotFoundHttpException();
        }

        $user = $request->user();
        $result = $this->acceptance->refuse($user, $sr);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result->toArray(), $result->isSuccess() ? 200 : 409);
        }

        $flashKey = $result->isSuccess() ? 'status' : 'error';

        return redirect()->route('provider.leads.index')->with($flashKey, $result->getMessage());
    }
}
