<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Matching\RoundStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminServiceRequestsController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly RoundStateMachine $stateMachine,
    ) {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');

        $query = ServiceRequest::withTrashed()->with(['wedRequest', 'unlockedBy'])->latest('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(25)->withQueryString();
        $statuses = collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all();

        return view('admin.service-requests.index', compact('items', 'status', 'statuses'));
    }

    public function show(string $serviceUid): View
    {
        $sr = ServiceRequest::withTrashed()->where('service_uid', $serviceUid)
            ->with(['wedRequest', 'unlockedBy'])
            ->first();

        if (! $sr) {
            throw new NotFoundHttpException();
        }

        // Verified, non-suspended providers offering this service — the pool an
        // admin can hand-assign the lead to (manual round-robin).
        $serviceType = $sr->service_type?->value ?? (string) $sr->service_type;
        $verifiedProviders = User::role('provider')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at'))
            ->whereHas('providerServices', fn ($q) => $q->where('service_type', $serviceType))
            ->with('providerProfile')
            ->orderBy('name')
            ->get();

        $statuses = collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all();

        return view('admin.service-requests.show', compact('sr', 'verifiedProviders', 'statuses'));
    }

    /**
     * Manually assign a verified provider to this lead (silent — no webhook).
     */
    public function assignProvider(Request $request, string $serviceUid): RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->firstOrFail();

        $validated = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:users,id'],
            'round'       => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);

        $provider = User::with('providerProfile')->findOrFail($validated['provider_id']);

        if (! $provider->providerProfile?->isActive()) {
            return back()->with('error', 'Ce prestataire doit être vérifié et actif pour être assigné.');
        }

        if ($sr->unlocked_by_user_id !== null) {
            return back()->with('error', 'Cette demande a déjà été acceptée par un prestataire.');
        }

        // Form values arrive as strings; the state machine is strictly typed.
        $round = filled($validated['round'] ?? null) ? (int) $validated['round'] : null;

        $this->stateMachine->assignProviderSilently($sr, (int) $provider->id, $round);

        Cache::forget("leads:count:{$provider->id}");
        $this->audit->log('service_request.assign_provider_manual', $sr, [
            'provider_id' => $provider->id,
            'round'       => $sr->current_round,
        ]);

        return back()->with('status', "Demande assignée à {$provider->name} (sans notification automatique).");
    }

    /**
     * Manually override the status of a service request.
     */
    public function updateStatus(Request $request, string $serviceUid): RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all())],
        ]);

        $old = $sr->status?->value ?? (string) $sr->status;
        $sr->status = $validated['status'];
        $sr->save();

        Cache::forget('leads:open_marketplace:version');
        $this->audit->log('service_request.update_status', $sr, ['from' => $old, 'to' => $validated['status']]);

        return back()->with('status', 'Statut mis à jour.');
    }

    /**
     * Manually set / clear the expiration date of a service request.
     */
    public function updateExpiry(Request $request, string $serviceUid): RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->firstOrFail();

        $validated = $request->validate([
            'expires_at' => ['nullable', 'date'],
        ]);

        $sr->expires_at = $validated['expires_at'] ?: null;
        $sr->save();

        $this->audit->log('service_request.update_expiry', $sr, ['expires_at' => (string) $sr->expires_at]);

        return back()->with('status', 'Date d\'expiration mise à jour.');
    }

    public function forceOpen(Request $request, string $serviceUid): RedirectResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->firstOrFail();

        if (! in_array($sr->status?->value, [
            RequestStatus::New->value,
            RequestStatus::Matching->value,
            RequestStatus::Round1->value,
            RequestStatus::Round2->value,
            RequestStatus::Round3->value,
        ], true)) {
            return back()->with('error', __('admin.service_requests.cannot_force_open'));
        }

        $this->stateMachine->openToMarketplace($sr);
        Cache::forget('leads:open_marketplace:version');

        $this->audit->log('service_request.force_open', $sr);

        return back()->with('status', __('admin.service_requests.force_opened_ok'));
    }
}
