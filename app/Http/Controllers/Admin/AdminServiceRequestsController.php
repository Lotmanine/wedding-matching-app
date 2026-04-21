<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
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

        return view('admin.service-requests.show', ['sr' => $sr]);
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
