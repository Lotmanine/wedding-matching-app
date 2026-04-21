<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function eligible(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_type'   => ['required', 'string'],
            'wilaya_code'    => ['required', 'string', 'size:2'],
            'exclude_ids'    => ['nullable', 'array'],
            'exclude_ids.*'  => ['integer'],
            'limit'          => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $exclude = array_map('intval', (array) ($data['exclude_ids'] ?? []));
        $limit = (int) ($data['limit'] ?? 10);

        $query = User::role('provider')
            ->where('status', 'active')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at'))
            ->whereHas('providerServices', fn ($q) => $q->where('service_type', $data['service_type']))
            ->whereHas('providerCoverage', fn ($q) => $q->where('wilaya_code', $data['wilaya_code']))
            ->with(['providerProfile', 'providerServices', 'providerCoverage'])
            ->leftJoin('provider_profiles', 'provider_profiles.user_id', '=', 'users.id')
            ->select('users.*')
            ->orderByDesc('provider_profiles.acceptance_rate')
            ->orderByDesc('provider_profiles.rating_avg')
            ->orderByDesc('provider_profiles.leads_accepted');

        if (! empty($exclude)) {
            $query->whereNotIn('users.id', $exclude);
        }

        $providers = $query->limit($limit)->get();

        return ApiResponse::success(ProviderResource::collection($providers)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $user = User::role('provider')
            ->where('id', $id)
            ->with(['providerProfile', 'providerServices', 'providerCoverage'])
            ->first();

        if (! $user) {
            return ApiResponse::error('not_found', 'Provider not found', [], 404);
        }

        return ApiResponse::success((new ProviderResource($user))->resolve(request()));
    }
}
