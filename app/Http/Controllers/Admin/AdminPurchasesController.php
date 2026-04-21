<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\AuditLogger;
use App\Services\Payments\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminPurchasesController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly PurchaseService $purchases,
    ) {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');

        $query = Purchase::with(['user', 'unlockPack'])->latest('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $purchases = $query->paginate(25)->withQueryString();

        return view('admin.purchases.index', compact('purchases', 'status'));
    }

    public function show(string $purchaseUid): View
    {
        $purchase = Purchase::where('purchase_uid', $purchaseUid)->with(['user', 'unlockPack', 'creditLedger'])->first();
        if (! $purchase) {
            throw new NotFoundHttpException();
        }

        return view('admin.purchases.show', compact('purchase'));
    }

    public function markPaid(Request $request, string $purchaseUid): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $purchase = Purchase::where('purchase_uid', $purchaseUid)->firstOrFail();

        if ($purchase->status === 'paid') {
            return back()->with('error', __('admin.purchases.already_paid'));
        }

        $this->purchases->markPaid($purchase, ['manual' => true, 'reason' => $request->input('reason')]);
        $this->audit->log('purchase.mark_paid_manual', $purchase, ['reason' => $request->input('reason')]);

        return back()->with('status', __('admin.purchases.marked_paid_ok'));
    }
}
