<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Whatsapp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Whatsapp\InboundButtonTapRequest;
use App\Http\Responses\ApiResponse;
use App\Models\WhatsappMessage;
use App\Services\AuditLogger;
use App\Services\Matching\LeadAcceptanceService;
use App\Support\Whatsapp\InboundButtonContext;
use App\Support\Whatsapp\InboundButtonException;
use App\Support\Whatsapp\SignedButtonPayload;
use Illuminate\Http\JsonResponse;

class InboundButtonTapController extends Controller
{
    public function __construct(
        private readonly LeadAcceptanceService $acceptance,
        private readonly AuditLogger $audit,
    ) {
    }

    public function __invoke(InboundButtonTapRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Idempotency
        $existing = WhatsappMessage::where('wa_message_id', $data['wa_message_id'])
            ->where('direction', 'inbound')
            ->first();

        if ($existing) {
            return ApiResponse::success([
                'status'              => 'already_processed',
                'service_request_uid' => $existing->service_request_uid,
                'provider_id'         => $existing->provider_id,
            ]);
        }

        // Decode + validate (typed exceptions)
        try {
            /** @var InboundButtonContext $ctx */
            $ctx = SignedButtonPayload::decodeAndValidate($data['button_payload'], $data['from_phone']);
        } catch (InboundButtonException $e) {
            $this->persistInbound(
                serviceRequestUid: 'unknown',
                providerId: null,
                fromPhone: $data['from_phone'],
                waMessageId: $data['wa_message_id'],
                payload: $data,
                status: 'failed',
                error: $e->reason,
            );

            $this->audit->log('whatsapp.button_tap.rejected', null, [
                'wa_message_id' => $data['wa_message_id'],
                'from_phone'    => $data['from_phone'],
                'reason'        => $e->reason,
            ]);

            return ApiResponse::success([
                'status' => 'rejected',
                'reason' => $e->reason,
            ]);
        }

        $row = $this->persistInbound(
            serviceRequestUid: $ctx->serviceRequest->service_uid,
            providerId: $ctx->provider->id,
            fromPhone: $data['from_phone'],
            waMessageId: $data['wa_message_id'],
            payload: $data,
            status: 'delivered',
        );

        if ($ctx->isAccept()) {
            $result = $this->acceptance->accept($ctx->provider, $ctx->serviceRequest, 'whatsapp');
            $row->forceFill(['template_intent' => 'inbound_accept'])->save();

            $this->audit->log('whatsapp.button_tap.accept', $ctx->serviceRequest, [
                'provider_id'    => $ctx->provider->id,
                'wa_message_id'  => $data['wa_message_id'],
                'result_status'  => $result->status,
            ]);

            return ApiResponse::success([
                'status'              => 'accepted',
                'service_request_uid' => $ctx->serviceRequest->service_uid,
                'provider_id'         => $ctx->provider->id,
                'result'              => $result->status,
            ]);
        }

        $result = $this->acceptance->refuse($ctx->provider, $ctx->serviceRequest);
        $row->forceFill(['template_intent' => 'inbound_refuse'])->save();

        $this->audit->log('whatsapp.button_tap.refuse', $ctx->serviceRequest, [
            'provider_id'   => $ctx->provider->id,
            'wa_message_id' => $data['wa_message_id'],
            'result_status' => $result->status,
        ]);

        return ApiResponse::success([
            'status'              => 'refused',
            'service_request_uid' => $ctx->serviceRequest->service_uid,
            'provider_id'         => $ctx->provider->id,
            'result'              => $result->status,
        ]);
    }

    private function persistInbound(
        string $serviceRequestUid,
        ?int $providerId,
        string $fromPhone,
        string $waMessageId,
        array $payload,
        string $status,
        ?string $error = null,
    ): WhatsappMessage {
        return WhatsappMessage::create([
            'service_request_uid'  => $serviceRequestUid,
            'provider_id'          => $providerId,
            'recipient_phone_e164' => $fromPhone,
            'direction'            => 'inbound',
            'event_type'           => 'button_tap',
            'template_intent'      => null,
            'role'                 => 'provider',
            'wa_message_id'        => $waMessageId,
            'status'               => $status,
            'error'                => $error,
            'payload_snapshot'     => $payload,
            'sent_at'              => now(),
        ]);
    }
}
