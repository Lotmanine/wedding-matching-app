<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\WedRequest;
use App\Models\Wilaya;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
