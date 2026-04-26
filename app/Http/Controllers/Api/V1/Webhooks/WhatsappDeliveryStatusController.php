<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\WhatsappMessage;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappDeliveryStatusController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event'                          => ['required', 'string', 'max:64'],
            'service_request_id'             => ['required', 'string', 'max:25'],
            'results'                        => ['required', 'array', 'min:1', 'max:200'],
            'results.*.role'                 => ['required', 'in:provider,couple'],
            'results.*.recipient_phone_e164' => ['required', 'string', 'max:20'],
            'results.*.template_intent'      => ['nullable', 'string', 'max:64'],
            'results.*.provider_id'          => ['nullable', 'integer'],
            'results.*.wa_message_id'        => ['nullable', 'string', 'max:120'],
            'results.*.status'               => ['required', 'in:queued,sent,delivered,read,failed'],
            'results.*.error'                => ['nullable', 'string', 'max:500'],
        ]);

        $written = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, &$written, &$skipped) {
            foreach ($data['results'] as $r) {
                $attrs = [
                    'service_request_uid'  => $data['service_request_id'],
                    'provider_id'          => $r['provider_id']     ?? null,
                    'recipient_phone_e164' => $r['recipient_phone_e164'],
                    'direction'            => 'outbound',
                    'event_type'           => $data['event'],
                    'template_intent'      => $r['template_intent'] ?? null,
                    'role'                 => $r['role'],
                    'wa_message_id'        => $r['wa_message_id']   ?? null,
                    'status'               => $r['status'],
                    'error'                => $r['error']           ?? null,
                    'sent_at'              => now(),
                ];

                if (! empty($r['wa_message_id'])) {
                    $existing = WhatsappMessage::where('wa_message_id', $r['wa_message_id'])->first();
                    if ($existing) {
                        $skipped++;
                        continue;
                    }
                }

                WhatsappMessage::create($attrs);
                $written++;
            }
        });

        $this->audit->log('whatsapp.delivery_status.recorded', null, [
            'service_request_id' => $data['service_request_id'],
            'event'              => $data['event'],
            'written'            => $written,
            'skipped'            => $skipped,
        ]);

        return ApiResponse::success([
            'recorded'  => $written,
            'duplicate' => $skipped,
        ]);
    }
}
