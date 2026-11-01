<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Enums\BudgetRange;
use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\LeadDispute;
use App\Models\ServiceRequest;
use App\Models\City;
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

        $filters = $request->only(['service_type', 'city_code', 'budget_range', 'sort']);

        if (! empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        if (! empty($filters['city_code'])) {
            $query->whereHas('wedRequest', fn ($q) => $q->where('couple_city_code', $filters['city_code']));
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

        $coverageCodes = $user->providerCoverage->pluck('city_code')
            ->map(fn ($v) => is_object($v) && property_exists($v, 'value') ? $v->value : (string) $v)
            ->values()
            ->all();
        $cityOptions = City::whereIn('code', $coverageCodes)->orderBy('name')->get();

        $budgetOptions = BudgetRange::cases();

        return view('provider.leads.index', [
            'leads'              => $leads,
            'filters'            => $filters,
            'serviceTypeOptions' => $serviceTypeOptions,
            'cityOptions'      => $cityOptions,
            'budgetOptions'      => $budgetOptions,
            'canReceive'         => $canReceive,
            'profile'            => $profile,
        ]);
    }

    /**
     * "Mes demandes acceptées" — every lead this provider unlocked. Contact
     * details are masked once the privacy anonymization window has passed.
     */
    public function accepted(Request $request)
    {
        $leads = ServiceRequest::where('unlocked_by_user_id', $request->user()->id)
            ->with('wedRequest')
            ->orderByDesc('unlocked_at')
            ->paginate(12);

        return view('provider.leads.accepted-list', ['leads' => $leads]);
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
                'dispute'    => LeadDispute::where('service_request_id', $sr->id)
                    ->where('provider_user_id', $user->id)
                    ->first(),
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
                    ->route('provider.credits.index')
                    ->with('error', __('credits.insufficient_redirect')),
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

    /**
     * Flag an unlocked lead as bad (litige). One dispute per lead per
     * provider; an admin reviews it and refunds the credits if justified.
     */
    public function dispute(Request $request, string $serviceUid): RedirectResponse
    {
        $validated = $request->validate([
            'reason'  => ['required', 'in:' . implode(',', LeadDispute::REASONS)],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $sr = ServiceRequest::where('service_uid', $serviceUid)->firstOrFail();

        if ($sr->unlocked_by_user_id !== $user->id) {
            abort(403);
        }

        $exists = LeadDispute::where('service_request_id', $sr->id)
            ->where('provider_user_id', $user->id)
            ->exists();
        if ($exists) {
            return back()->with('error', __('Un signalement existe déjà pour cette demande.'));
        }

        LeadDispute::create([
            'service_request_id' => $sr->id,
            'provider_user_id'   => $user->id,
            'reason'             => $validated['reason'],
            'details'            => $validated['details'] ?? null,
        ]);

        return back()->with('status', __("Signalement envoyé. Notre équipe va l'examiner."));
    }
}
