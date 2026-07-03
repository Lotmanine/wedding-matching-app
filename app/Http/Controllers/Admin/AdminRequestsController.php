<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use App\Models\Wilaya;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminRequestsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $wilaya = (string) $request->query('wilaya', '');

        $query = WedRequest::withTrashed()->with('serviceRequests')->latest('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($wilaya !== '') {
            $query->where('couple_wilaya_code', $wilaya);
        }

        $requests = $query->paginate(25)->withQueryString();
        $wilayas = Wilaya::active()->get();
        $statuses = collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all();

        return view('admin.requests.index', compact('requests', 'status', 'wilaya', 'wilayas', 'statuses'));
    }

    public function show(string $wedUid): View
    {
        $wr = WedRequest::withTrashed()->where('wed_uid', $wedUid)
            ->with(['serviceRequests', 'serviceRequests.unlockedBy'])
            ->first();

        if (! $wr) {
            throw new NotFoundHttpException();
        }

        return view('admin.requests.show', ['wedRequest' => $wr]);
    }

    public function edit(string $wedUid): View
    {
        $wr = WedRequest::withTrashed()->where('wed_uid', $wedUid)->with('serviceRequests')->first();

        if (! $wr) {
            throw new NotFoundHttpException();
        }

        return view('admin.requests.edit', [
            'wr'            => $wr,
            'wilayas'      => Wilaya::active()->get(),
            'guestCounts'  => \App\Enums\GuestCount::cases(),
            'budgetRanges' => \App\Enums\BudgetRange::cases(),
            'serviceTypes' => \App\Enums\ServiceType::cases(),
            'statuses'     => collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all(),
            // Services whose child request was accepted cannot be removed.
            'lockedServices' => $wr->serviceRequests
                ->whereNotNull('unlocked_by_user_id')
                ->map(fn ($sr) => $sr->service_type?->value ?? (string) $sr->service_type)
                ->values()
                ->all(),
        ]);
    }

    public function update(Request $request, string $wedUid): RedirectResponse
    {
        $wr = WedRequest::withTrashed()->where('wed_uid', $wedUid)->with('serviceRequests')->firstOrFail();

        $gcValues  = collect(\App\Enums\GuestCount::cases())->pluck('value')->all();
        $brValues  = collect(\App\Enums\BudgetRange::cases())->pluck('value')->all();
        $svcValues = collect(\App\Enums\ServiceType::cases())->pluck('value')->all();
        $stValues  = collect(RequestStatus::cases())->map(fn ($c) => $c->value)->all();

        $validated = $request->validate([
            'couple_first_name'  => ['required', 'string', 'min:2', 'max:50'],
            'couple_whatsapp'    => ['required', 'string', 'regex:/^\+213[567]\d{8}$/'],
            'couple_wilaya_code' => ['required', 'string', 'in:' . Wilaya::pluck('code')->implode(',')],
            'couple_commune'     => ['nullable', 'string', 'max:120'],
            'wedding_date'       => ['nullable', 'date'],
            'wedding_season'     => ['nullable', 'in:Printemps,Été,Automne,Hiver'],
            'guest_count'        => ['nullable', 'in:' . implode(',', $gcValues)],
            'budget_range'       => ['nullable', 'in:' . implode(',', $brValues)],
            'description'        => ['nullable', 'string', 'max:500'],
            'status'             => ['required', 'in:' . implode(',', $stValues)],
            'expires_at'         => ['nullable', 'date'],
            'services_needed'    => ['required', 'array', 'min:1', 'max:8'],
            'services_needed.*'  => ['string', 'in:' . implode(',', $svcValues)],
        ]);

        $newServices = array_values($validated['services_needed']);
        $oldServices = array_values((array) $wr->services_needed);

        // A service whose child request was already accepted (credits spent)
        // cannot be removed — refuse the whole update so nothing half-applies.
        $accepted = $wr->serviceRequests
            ->whereNotNull('unlocked_by_user_id')
            ->toBase() // plain collection of strings — Eloquent's diff() expects models
            ->map(fn ($sr) => $sr->service_type?->value ?? (string) $sr->service_type)
            ->values();
        $blocked = $accepted->diff($newServices);
        if ($blocked->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'services_needed' => 'Impossible de retirer ' . $blocked->implode(', ') . ' : la demande a déjà été acceptée par un prestataire.',
            ]);
        }

        DB::transaction(function () use ($wr, $validated, $newServices, $oldServices) {
            $originalExpiry = $wr->expires_at;
            $wr->fill($validated);
            $wr->services_needed = $newServices;
            // Blank expiry field means "leave unchanged", not "clear it".
            if (blank($validated['expires_at'] ?? null)) {
                $wr->expires_at = $originalExpiry;
            }
            $wr->save();

            // Sync child service requests with the edited services list.
            foreach (array_diff($newServices, $oldServices) as $added) {
                // Recreate only if no live child of that type exists.
                $exists = $wr->serviceRequests->first(
                    fn ($sr) => ($sr->service_type?->value ?? (string) $sr->service_type) === $added && $sr->deleted_at === null
                );
                if (! $exists) {
                    ServiceRequest::create([
                        'wed_request_id' => $wr->id,
                        'service_type'   => $added,
                        'status'         => RequestStatus::New->value,
                        'current_round'  => 0,
                    ]);
                }
            }

            foreach (array_diff($oldServices, $newServices) as $removed) {
                $wr->serviceRequests
                    ->filter(fn ($sr) => ($sr->service_type?->value ?? (string) $sr->service_type) === $removed && $sr->unlocked_by_user_id === null)
                    ->each(function ($sr) {
                        $sr->status = RequestStatus::Deleted->value;
                        $sr->save();
                        $sr->delete(); // soft delete
                    });
            }
        });

        $this->audit->log('wed_request.update', $wr, [
            'fields'           => array_keys($validated),
            'services_added'   => array_values(array_diff($newServices, $oldServices)),
            'services_removed' => array_values(array_diff($oldServices, $newServices)),
        ]);

        return redirect()->route('admin.requests.show', $wr->wed_uid)->with('status', 'Demande mise à jour.');
    }

    public function expire(Request $request, string $wedUid): RedirectResponse
    {
        $wr = WedRequest::where('wed_uid', $wedUid)->firstOrFail();
        $wr->forceFill([
            'status'     => RequestStatus::Expired->value,
            'expires_at' => now()->subSecond(),
        ])->save();

        $this->audit->log('wed_request.expire', $wr);

        return back()->with('status', __('admin.requests.expired_ok'));
    }

    public function delete(Request $request, string $wedUid): RedirectResponse
    {
        $request->validate([
            'confirm'   => ['required', 'in:SUPPRIMER'],
            'reason'    => ['required', 'string', 'max:500'],
        ]);

        $wr = WedRequest::withTrashed()->where('wed_uid', $wedUid)->firstOrFail();

        $this->audit->log('wed_request.delete', $wr, [
            'wed_uid' => $wr->wed_uid,
            'reason'  => $request->input('reason'),
        ]);

        $wr->serviceRequests()->forceDelete();
        $wr->forceDelete();

        return redirect()->route('admin.requests.index')->with('status', __('admin.requests.deleted_ok'));
    }
}
