<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Whatsapp\InboundStatusUpdateRequest;
use App\Http\Responses\ApiResponse;
use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;

class InboundStatusUpdateController extends Controller
{
    /**
     * Status progression chain: sent < delivered < read.
     * `failed` terminal — always wins.
     */
    private const RANK = [
        'queued'    => 0,
        'sent'      => 1,
        'delivered' => 2,
        'read'      => 3,
    ];

    public function __invoke(InboundStatusUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $row = WhatsappMessage::where('wa_message_id', $data['wa_message_id'])->first();
        if (! $row) {
            return ApiResponse::success([
                'status'        => 'unknown_wamid',
                'wa_message_id' => $data['wa_message_id'],
            ]);
        }

        $newStatus = $data['status'];
        $current   = $row->status;

        // Idempotent / no regression
        if ($newStatus === $current) {
            return ApiResponse::success([
                'status'        => 'no_change',
                'wa_message_id' => $row->wa_message_id,
                'current'       => $current,
            ]);
        }

        $shouldUpdate = match (true) {
            $newStatus === 'failed'                                                              => true,
            $current   === 'failed'                                                              => false,
            isset(self::RANK[$newStatus], self::RANK[$current]) && self::RANK[$newStatus] > self::RANK[$current] => true,
            default                                                                              => false,
        };

        if (! $shouldUpdate) {
            return ApiResponse::success([
                'status'        => 'regression_blocked',
                'wa_message_id' => $row->wa_message_id,
                'current'       => $current,
            ]);
        }

        $row->forceFill([
            'status' => $newStatus,
            'error'  => $data['error'] ?? $row->error,
        ])->save();

        return ApiResponse::success([
            'status'        => 'updated',
            'wa_message_id' => $row->wa_message_id,
            'previous'      => $current,
            'current'       => $newStatus,
        ]);
    }
}
