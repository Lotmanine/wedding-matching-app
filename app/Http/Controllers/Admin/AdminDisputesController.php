<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadDispute;
use App\Services\AuditLogger;
use App\Services\Credits\CreditsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Credit-back queue: review provider "bad lead" flags. Approving refunds the
 * credits charged for the unlock; rejecting records a note shown back to the
 * provider. Accessible to admins and managers (day-to-day ops).
 */
class AdminDisputesController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly CreditsService $credits,
    ) {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'pending');

        $query = LeadDispute::with(['serviceRequest.wedRequest', 'provider.providerProfile'])
            ->latest('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.disputes.index', [
            'disputes' => $query->paginate(25)->withQueryString(),
            'status'   => $status,
            'counts'   => [
                'pending'  => LeadDispute::where('status', 'pending')->count(),
                'approved' => LeadDispute::where('status', 'approved')->count(),
                'rejected' => LeadDispute::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $result = DB::transaction(function () use ($request, $id) {
            $dispute = LeadDispute::lockForUpdate()->with(['serviceRequest', 'provider'])->findOrFail($id);

            if (! $dispute->isPending()) {
                return 'not_pending';
            }

            $credits = max(1, (int) ($dispute->serviceRequest?->credits_charged ?? 1));

            $ledger = $this->credits->add(
                $dispute->provider,
                $credits,
                'refund',
                $dispute,
                'Dispute refund ' . $dispute->serviceRequest?->service_uid,
            );

            $dispute->forceFill([
                'status'             => 'approved',
                'decided_by_user_id' => $request->user()->id,
                'decided_at'         => now(),
                'credit_ledger_id'   => $ledger->id,
            ])->save();

            $this->audit->log('dispute.approve', $dispute, [
                'credits_refunded' => $credits,
                'provider_id'      => $dispute->provider_user_id,
            ]);

            return 'ok';
        });

        return $result === 'ok'
            ? back()->with('status', __('Dispute approved: credits refunded to the provider.'))
            : back()->with('error', __('This dispute has already been handled.'));
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        $dispute = LeadDispute::findOrFail($id);

        if (! $dispute->isPending()) {
            return back()->with('error', __('This dispute has already been handled.'));
        }

        $dispute->forceFill([
            'status'             => 'rejected',
            'admin_note'         => $validated['admin_note'],
            'decided_by_user_id' => $request->user()->id,
            'decided_at'         => now(),
        ])->save();

        $this->audit->log('dispute.reject', $dispute, ['note' => $validated['admin_note']]);

        return back()->with('status', __('Dispute rejected.'));
    }
}
