<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\N8nHeartbeat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class N8nHeartbeatController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'run_at'                                  => ['required', 'string'],
            'severity'                                => ['required', 'in:none,warn,critical'],
            'snapshot_summary'                        => ['required', 'array'],
            'snapshot_summary.stuck_matching'         => ['nullable'],
            'snapshot_summary.failed_webhooks_24h'    => ['nullable'],
            'snapshot_summary.failed_whatsapp_24h'    => ['nullable'],
            'snapshot_summary.n8n_errors_24h'         => ['nullable'],
            'alerts_sent'                             => ['required', 'array'],
        ]);

        $beat = N8nHeartbeat::create([
            'run_at'           => Carbon::parse($data['run_at']),
            'severity'         => $data['severity'],
            'snapshot_summary' => $data['snapshot_summary'],
            'alerts_sent'      => $data['alerts_sent'],
        ]);

        return ApiResponse::success([
            'status' => 'recorded',
            'id'     => $beat->id,
        ]);
    }
}
