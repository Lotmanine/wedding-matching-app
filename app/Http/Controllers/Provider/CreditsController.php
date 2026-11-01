<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\CreditLedger;
use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Services\Payments\ChargilyClient;
use App\Services\Payments\PaymentSettings;
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
        private readonly PaymentSettings $paymentSettings,
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
            'canBuy'  => (bool) $user->providerProfile?->canPurchaseCredits(),
        ]);
    }

    /**
     * Payment-method choice page (logged-in providers only). Shows the enabled
     * methods (online Chargily and/or offline versement) for a pack.
     */
    public function pay(Request $request, string $packSlug)
    {
        $user = $request->user()->load('providerProfile');

        if (! $user->providerProfile?->canPurchaseCredits()) {
            return redirect()->route('provider.credits.index')
                ->with('error', __('credits.checkout.not_verified'));
        }

        $pack = UnlockPack::where('slug', $packSlug)->where('active', true)->first();
        if (! $pack) {
            throw new NotFoundHttpException('Pack introuvable ou inactif.');
        }

        $methods = $this->paymentSettings->enabledMethods();
        if ($methods === []) {
            return redirect()->route('provider.credits.index')
                ->with('error', __('credits.checkout.no_method'));
        }

        return view('provider.credits.pay', [
            'pack'           => $pack,
            'methods'        => $methods,
            'offlineDetails' => $this->paymentSettings->offlineDetails(),
        ]);
    }

    /**
     * Start an online Chargily checkout and redirect to the gateway.
     */
    public function checkout(Request $request, string $packSlug): RedirectResponse
    {
        $user = $request->user()->load('providerProfile');

        // Only verified, non-suspended providers may buy credits. Otherwise a
        // provider could accumulate credits they are not allowed to spend.
        if (! $user->providerProfile?->canPurchaseCredits()) {
            return redirect()->route('provider.credits.index')
                ->with('error', __('credits.checkout.not_verified'));
        }

        if (! $this->paymentSettings->isMethodEnabled(PaymentSettings::METHOD_ONLINE)) {
            return redirect()->route('provider.credits.index')->with('error', __('credits.checkout.no_method'));
        }

        $pack = UnlockPack::where('slug', $packSlug)->where('active', true)->first();
        if (! $pack) {
            throw new NotFoundHttpException('Pack introuvable ou inactif.');
        }

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

    /**
     * Record an offline (bank transfer / CCP) payment declaration. Credits are
     * NOT granted here — the purchase waits in 'pending_verification' for an
     * admin to confirm the funds arrived.
     */
    public function offlineSubmit(Request $request, string $packSlug): RedirectResponse
    {
        $user = $request->user()->load('providerProfile');

        if (! $user->providerProfile?->canPurchaseCredits()) {
            return redirect()->route('provider.credits.index')
                ->with('error', __('credits.checkout.not_verified'));
        }

        if (! $this->paymentSettings->isMethodEnabled(PaymentSettings::METHOD_OFFLINE)) {
            return redirect()->route('provider.credits.index')->with('error', __('credits.checkout.no_method'));
        }

        $pack = UnlockPack::where('slug', $packSlug)->where('active', true)->first();
        if (! $pack) {
            throw new NotFoundHttpException('Pack introuvable ou inactif.');
        }

        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'min:3', 'max:120'],
            'proof'             => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'local');
        }

        $purchase = $this->purchases->initiateOffline($user, $pack, $validated['payment_reference'], $proofPath);

        return redirect()->route('provider.credits.purchase.show', $purchase->purchase_uid)
            ->with('status', __('credits.offline.submitted'));
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

        // Stored reasons are technical codes (gateway_failed, user_cancelled,
        // checkout_init_failed) or "offline_rejected: <reason typed by an admin>".
        // The page shows a sentence in the reader's language, never the code.
        $reason = trim((string) $purchase?->failure_reason);
        $code = $reason === '' ? null : trim(explode(':', $reason, 2)[0]);
        $known = ['gateway_failed', 'user_cancelled', 'checkout_init_failed', 'offline_rejected'];

        $explanation = null;
        if ($code !== null) {
            $explanation = __('credits.failure_reasons.' . (in_array($code, $known, true) ? $code : 'unknown'));
        }

        // Free text typed by the team when rejecting a versement: shown as stored.
        $teamNote = $code === 'offline_rejected' && str_contains($reason, ':')
            ? trim(explode(':', $reason, 2)[1])
            : '';

        return view('provider.credits.failure', [
            'purchase'       => $purchase,
            'explanation'    => $explanation,
            'teamNote'       => $teamNote,
            // Card-related causes only make sense for a card payment that failed.
            'showCardCauses' => $code === null || $code === 'gateway_failed',
            'offlineEnabled' => $this->paymentSettings->isMethodEnabled(PaymentSettings::METHOD_OFFLINE),
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
            // The PDF receipt is always issued in French.
            app()->setLocale('fr');
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
            fputcsv($handle, [__('Date'), __('Type'), __('Mouvement'), __('Solde'), __('Description')]);
            foreach ($entries as $e) {
                fputcsv($handle, [
                    $e->created_at?->toDateTimeString(),
                    $e->typeLabel(),
                    $e->delta,
                    $e->balance_after,
                    $e->plainDescription(),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
