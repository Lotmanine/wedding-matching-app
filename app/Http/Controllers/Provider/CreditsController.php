<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\CreditLedger;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Services\Payments\ChargilyClient;
use App\Services\Payments\PurchaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreditsController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchases,
        private readonly ChargilyClient $chargily,
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user()->load('providerProfile');
        $packs = UnlockPack::active()->get();
        $recent = CreditLedger::where('user_id', $user->id)->latest('created_at')->limit(5)->get();

        return view('provider.credits.index', [
            'user'    => $user,
            'profile' => $user->providerProfile,
            'packs'   => $packs,
            'recent'  => $recent,
        ]);
    }

    public function checkout(Request $request, string $packSlug): RedirectResponse
    {
        $pack = UnlockPack::where('slug', $packSlug)->where('active', true)->first();
        if (! $pack) {
            throw new NotFoundHttpException('Pack introuvable ou inactif.');
        }

        $user = $request->user();
        $purchase = $this->purchases->initiate($user, $pack);

        try {
            $checkout = $this->chargily->createCheckout($purchase);
        } catch (\Throwable $e) {
            Log::error('Checkout creation failed', ['error' => $e->getMessage(), 'purchase' => $purchase->purchase_uid]);
            $purchase->forceFill(['status' => 'failed', 'failed_at' => now(), 'failure_reason' => 'checkout_init_failed'])->save();

            return redirect()->route('provider.credits.index')->with('error', __('credits.checkout.failed'));
        }

        $purchase->forceFill([
            'gateway_checkout_id' => $checkout['id'],
            'gateway_metadata'    => $checkout['raw'],
        ])->save();

        if (! empty($checkout['checkout_url'])) {
            return redirect()->away($checkout['checkout_url']);
        }

        return redirect()->route('provider.credits.index')->with('error', __('credits.checkout.no_url'));
    }

    public function success(Request $request)
    {
        $uid = (string) $request->query('purchase');
        $purchase = Purchase::where('purchase_uid', $uid)->first();

        if (! $purchase || $purchase->user_id !== $request->user()->id) {
            throw new NotFoundHttpException();
        }

        return view('provider.credits.success', [
            'purchase' => $purchase->load('unlockPack'),
            'profile'  => $request->user()->load('providerProfile')->providerProfile,
        ]);
    }

    public function failure(Request $request)
    {
        $uid = (string) $request->query('purchase');
        $purchase = $uid !== '' ? Purchase::where('purchase_uid', $uid)->where('user_id', $request->user()->id)->first() : null;

        return view('provider.credits.failure', [
            'purchase' => $purchase,
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $type = (string) $request->query('type', 'all');

        $query = CreditLedger::where('user_id', $user->id);

        if ($type !== 'all' && $type !== '') {
            $query->where('type', $type);
        }

        if ($request->query('format') === 'csv') {
            return $this->exportCsv($query->latest('created_at')->get(), $user->id);
        }

        $entries = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('provider.credits.history', [
            'entries' => $entries,
            'type'    => $type,
        ]);
    }

    public function showPurchase(Request $request, string $purchaseUid)
    {
        $purchase = Purchase::where('purchase_uid', $purchaseUid)
            ->where('user_id', $request->user()->id)
            ->with(['unlockPack', 'user'])
            ->first();

        if (! $purchase) {
            throw new NotFoundHttpException();
        }

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('provider.credits.receipt-pdf', ['purchase' => $purchase]);

            return $pdf->download('recu-' . $purchase->purchase_uid . '.pdf');
        }

        return view('provider.credits.purchase-show', [
            'purchase' => $purchase,
        ]);
    }

    private function exportCsv($entries, int $userId): StreamedResponse
    {
        $filename = 'transactions-' . $userId . '-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Delta', 'Solde après', 'Notes']);
            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->created_at?->toDateTimeString(),
                    $e->type,
                    $e->delta,
                    $e->balance_after,
                    $e->notes ?? '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
