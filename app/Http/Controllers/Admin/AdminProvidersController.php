<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\City;
use App\Services\AuditLogger;
use App\Services\Credits\CreditsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminProvidersController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly CreditsService $credits,
    ) {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $city = (string) $request->query('city', '');
        $service = (string) $request->query('service', '');
        $search = (string) $request->query('q', '');

        $query = User::role('provider')
            ->with(['providerProfile', 'providerServices']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            match ($status) {
                'verified'   => $query->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at')),
                'pending'    => $query->whereHas('providerProfile', fn ($q) => $q->where('verified', false)->whereNull('suspended_at')),
                'suspended'  => $query->whereHas('providerProfile', fn ($q) => $q->whereNotNull('suspended_at')),
                default      => null,
            };
        }

        if ($city !== '') {
            $query->whereHas('providerProfile', fn ($q) => $q->where('primary_city_code', $city));
        }

        if ($service !== '') {
            $query->whereHas('providerServices', fn ($q) => $q->where('service_type', $service));
        }

        $providers = $query->latest('id')->paginate(25)->withQueryString();
        $cities = City::active()->get();

        return view('admin.providers.index', compact('providers', 'status', 'city', 'service', 'search', 'cities'));
    }

    public function show(int $id): View
    {
        $user = User::with(['providerProfile', 'providerServices', 'providerCoverage', 'creditLedger' => fn ($q) => $q->latest('created_at')->limit(20)])
            ->find($id);

        if (! $user || ! $user->hasRole('provider')) {
            throw new NotFoundHttpException();
        }

        $purchases = \App\Models\Purchase::where('user_id', $id)->latest('created_at')->limit(10)->get();
        $acceptedLeads = \App\Models\ServiceRequest::where('unlocked_by_user_id', $id)->latest('unlocked_at')->limit(10)->get();

        return view('admin.providers.show', compact('user', 'purchases', 'acceptedLeads'));
    }

    public function verify(Request $request, int $id): RedirectResponse
    {
        $profile = ProviderProfile::where('user_id', $id)->firstOrFail();
        $profile->forceFill(['verified' => true, 'verified_at' => now()])->save();

        // Keep the account status in sync: registration leaves users.status at
        // 'pending_verification' and lead eligibility requires 'active', so a
        // verified provider must also be activated (suspended accounts are
        // left untouched — reactivate() is the explicit path for those).
        User::where('id', $id)->where('status', 'pending_verification')->update(['status' => 'active']);

        $this->audit->log('provider.verify', $profile, ['user_id' => $id]);

        return back()->with('status', __('admin.providers.verified_ok'));
    }

    public function unverify(Request $request, int $id): RedirectResponse
    {
        $profile = ProviderProfile::where('user_id', $id)->firstOrFail();
        $profile->forceFill(['verified' => false, 'verified_at' => null])->save();

        // Mirror of verify(): the account goes back to pending review so the
        // Users tab and the provider badge tell the same story. Suspended
        // accounts keep their 'suspended' status.
        User::where('id', $id)->where('status', 'active')->update(['status' => 'pending_verification']);

        $this->audit->log('provider.unverify', $profile, ['user_id' => $id]);

        return back()->with('status', __('admin.providers.unverified_ok'));
    }

    public function suspend(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $profile = ProviderProfile::where('user_id', $id)->firstOrFail();
        $profile->forceFill([
            'suspended_at'     => now(),
            'suspended_reason' => $data['reason'],
        ])->save();

        User::where('id', $id)->update(['status' => 'suspended']);

        $this->audit->log('provider.suspend', $profile, ['reason' => $data['reason']]);

        return back()->with('status', __('admin.providers.suspended_ok'));
    }

    public function reactivate(Request $request, int $id): RedirectResponse
    {
        $profile = ProviderProfile::where('user_id', $id)->firstOrFail();
        $profile->forceFill(['suspended_at' => null, 'suspended_reason' => null])->save();

        User::where('id', $id)->update(['status' => 'active']);

        $this->audit->log('provider.reactivate', $profile);

        return back()->with('status', __('admin.providers.reactivated_ok'));
    }

    public function adjustCredits(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::findOrFail($id);

        // The validated value arrives as a string from the form; CreditsService
        // is strictly typed (int $amount) so cast once here.
        $amount = (int) $data['amount'];

        try {
            if ($amount > 0) {
                $ledger = $this->credits->add($user, $amount, 'admin_adjustment', null, $data['reason']);
            } else {
                $ledger = $this->credits->deduct($user, abs($amount), 'admin_adjustment', null, $data['reason']);
            }
        } catch (\App\Exceptions\InsufficientCreditsException $e) {
            return back()->withErrors(['amount' => __('admin.providers.credits_insufficient', ['available' => $e->available])]);
        }

        $ledger->forceFill(['created_by_user_id' => $request->user()->id])->save();

        $this->audit->log('provider.credits.adjust', $user, [
            'amount' => $data['amount'],
            'reason' => $data['reason'],
            'ledger_id' => $ledger->id,
        ]);

        return back()->with('status', trans_choice('admin.providers.credits_adjusted_ok', abs((int) $data['amount']), ['amount' => $data['amount']]));
    }
}
