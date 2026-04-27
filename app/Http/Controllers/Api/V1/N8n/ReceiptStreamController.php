<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\N8n;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceiptStreamController extends Controller
{
    public function show(string $purchaseUid): Response
    {
        $purchase = Purchase::where('purchase_uid', $purchaseUid)->with(['user', 'unlockPack'])->first();
        if (! $purchase) {
            return response()->json(['error' => ['code' => 'not_found', 'message' => 'Purchase not found']], 404);
        }

        // Reuse the existing receipt template from the provider-facing receipt page (Prompt 7).
        $pdfBytes = Pdf::loadView('provider.credits.receipt-pdf', ['purchase' => $purchase])->output();

        return response()->make($pdfBytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $purchase->purchase_uid . '.pdf"',
            'Cache-Control'       => 'no-store',
        ]);
    }

    public function markEmailed(Request $request, string $purchaseUid): JsonResponse
    {
        $data = $request->validate([
            'sent_at'         => ['required', 'string'],
            'smtp_message_id' => ['nullable', 'string', 'max:255'],
        ]);

        $purchase = Purchase::where('purchase_uid', $purchaseUid)->first();
        if (! $purchase) {
            return ApiResponse::error('not_found', 'Purchase not found', [], 404);
        }

        if ($purchase->receipt_emailed_at !== null) {
            return ApiResponse::success([
                'status'             => 'already_marked',
                'receipt_emailed_at' => $purchase->receipt_emailed_at?->toAtomString(),
            ]);
        }

        $purchase->forceFill([
            'receipt_emailed_at' => \Illuminate\Support\Carbon::parse($data['sent_at']),
            'smtp_message_id'    => $data['smtp_message_id'] ?? null,
        ])->save();

        return ApiResponse::success([
            'status' => 'marked',
        ]);
    }
}
