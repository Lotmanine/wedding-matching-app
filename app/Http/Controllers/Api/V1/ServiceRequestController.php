<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Responses\ApiResponse;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Matching\LeadAcceptanceService;
use App\Services\Matching\Results\AcceptResult;
use App\Services\Matching\RoundStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceRequestController extends Controller
{
    public function __construct(
        private readonly RoundStateMachine $stateMachine,
        private readonly LeadAcceptanceService $acceptance,
        private readonly AuditLogger $audit,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $query = ServiceRequest::query()->with('wedRequest');

        if ($s = $request->query('status')) {
            $query->where('status', $s);
        }
        if ($t = $request->query('service_type')) {
            $query->where('service_type', $t);
        }
        if ($w = $request->query('city_code')) {
            $query->whereHas('wedRequest', fn ($q) => $q->where('couple_city_code', $w));
        }

        $limit = min((int) $request->query('limit', 25), 100);
        $rows = $query->latest('created_at')->limit($limit)->get();

        return ApiResponse::success(ServiceRequestResource::collection($rows)->resolve());
    }

    public function pendingMatching(Request $request): JsonResponse
    {
        $rows = ServiceRequest::where('status', RequestStatus::Matching->value)
            ->with('wedRequest')
            ->limit(100)
            ->get();

        return ApiResponse::success(ServiceRequestResource::collection($rows)->resolve());
    }

    public function show(string $serviceUid): JsonResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->with(['wedRequest', 'wedRequest.serviceRequests'])->first();
        if (! $sr) {
            return ApiResponse::error('not_found', 'Service request not found', [], 404);
        }

        return ApiResponse::success((new ServiceRequestResource($sr))->resolve(request()));
    }

    public function startRound(Request $request, string $serviceUid): JsonResponse
    {
        $data = $request->validate([
            'round'         => ['required', 'integer', 'between:1,3'],
            // 10 = the highest "providers per round" /admin/settings accepts.
            'provider_ids'  => ['required', 'array', 'min:1', 'max:10'],
            'provider_ids.*'=> ['integer', 'exists:users,id'],
        ]);

        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            return ApiResponse::error('not_found', 'Service request not found', [], 404);
        }

        if ($sr->current_round >= $data['round']) {
            return ApiResponse::error('already_advanced', 'Service request already past this round', [
                'current_round'  => $sr->current_round,
                'current_status' => $sr->status?->value,
            ], 409);
        }

        $this->stateMachine->startRound($sr, (int) $data['round'], $data['provider_ids']);
        $sr->refresh();

        $this->audit->log('api.service_request.start_round', $sr, [
            'round'        => $data['round'],
            'provider_ids' => $data['provider_ids'],
        ]);

        Cache::forget('leads:open_marketplace:version');

        return ApiResponse::success((new ServiceRequestResource($sr))->resolve(request()));
    }

    public function accept(Request $request, string $serviceUid): JsonResponse
    {
        $data = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:users,id'],
            'via'         => ['required', 'in:whatsapp,api,dashboard'],
        ]);

        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            return ApiResponse::error('not_found', 'Service request not found', [], 404);
        }

        $provider = User::with(['providerProfile', 'providerServices', 'providerCoverage'])->find($data['provider_id']);
        $result = $this->acceptance->accept($provider, $sr, $data['via']);

        $this->audit->log('api.service_request.accept', $sr, [
            'provider_id' => $data['provider_id'],
            'via'         => $data['via'],
            'result'      => $result->status,
        ]);

        return match ($result->status) {
            AcceptResult::STATUS_SUCCESS              => ApiResponse::success($result->toArray()),
            AcceptResult::STATUS_ALREADY_TAKEN        => ApiResponse::error($result->status, $result->getMessage(), [], 409),
            AcceptResult::STATUS_EXPIRED              => ApiResponse::error($result->status, $result->getMessage(), [], 410),
            AcceptResult::STATUS_NOT_ELIGIBLE         => ApiResponse::error($result->status, $result->getMessage(), [], 403),
            AcceptResult::STATUS_INSUFFICIENT_CREDITS => ApiResponse::error($result->status, $result->getMessage(), [
                'required'  => $result->cost,
                'available' => $result->available,
            ], 402),
            default                                   => ApiResponse::error('unknown', 'Unknown result status', [], 500),
        };
    }

    public function refuse(Request $request, string $serviceUid): JsonResponse
    {
        $data = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:users,id'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ]);

        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            return ApiResponse::error('not_found', 'Service request not found', [], 404);
        }

        $provider = User::with(['providerProfile'])->find($data['provider_id']);
        $result = $this->acceptance->refuse($provider, $sr);

        $this->audit->log('api.service_request.refuse', $sr, [
            'provider_id' => $data['provider_id'],
            'reason'      => $data['reason'] ?? null,
            'result'      => $result->status,
        ]);

        return $result->isSuccess()
            ? ApiResponse::success($result->toArray())
            : ApiResponse::error($result->status, $result->getMessage(), [], 409);
    }

    public function forceOpen(string $serviceUid): JsonResponse
    {
        $sr = ServiceRequest::where('service_uid', $serviceUid)->first();
        if (! $sr) {
            return ApiResponse::error('not_found', 'Service request not found', [], 404);
        }

        if (! in_array($sr->status?->value, ['new', 'matching', 'round1', 'round2', 'round3'], true)) {
            return ApiResponse::error('invalid_state', 'Cannot force-open from current status', [
                'status' => $sr->status?->value,
            ], 409);
        }

        $this->stateMachine->openToMarketplace($sr);
        Cache::forget('leads:open_marketplace:version');
        $sr->refresh();

        $this->audit->log('api.service_request.force_open', $sr);

        return ApiResponse::success((new ServiceRequestResource($sr))->resolve(request()));
    }
}
