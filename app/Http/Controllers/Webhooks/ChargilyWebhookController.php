<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Services\Payments\ChargilyClient;
use App\Services\Payments\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChargilyWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        ChargilyClient $chargily,
        PurchaseService $purchases,
    ): JsonResponse {
        $signature = $request->header('signature') ?? $request->header('Signature');

        if (! $chargily->verifyWebhookSignature($signature, $request->getContent())) {
            Log::warning('Chargily webhook: invalid signature', [
                'ip'   => $request->ip(),
                'size' => strlen($request->getContent()),
            ]);

            return response()->json(['error' => 'invalid signature'], 401);
        }

        $event = (string) $request->input('type');
        $data = (array) $request->input('data', []);

        $checkoutId = $data['id'] ?? $data['checkout_id'] ?? null;
        if (empty($checkoutId)) {
            Log::info('Chargily webhook: missing checkout id', ['event' => $event]);

            return response()->json(['error' => 'missing checkout id'], 422);
        }

        $purchase = Purchase::where('gateway_checkout_id', $checkoutId)->first();
        if (! $purchase) {
            Log::warning('Chargily webhook: purchase not found', [
                'event'       => $event,
                'checkout_id' => $checkoutId,
            ]);

            return response()->json(['error' => 'not found'], 404);
        }

        Log::info('Chargily webhook received', [
            'event'        => $event,
            'purchase_uid' => $purchase->purchase_uid,
            'mode'         => config('wedding.chargily.mode'),
        ]);

        match ($event) {
            'checkout.paid'      => $purchases->markPaid($purchase, $data),
            'checkout.failed'    => $purchases->markFailed($purchase, 'gateway_failed', $data),
            'checkout.cancelled' => $purchases->markFailed($purchase, 'user_cancelled', $data),
            default              => Log::info('Chargily webhook: unhandled event', ['event' => $event]),
        };

        return response()->json(['received' => true]);
    }
}
