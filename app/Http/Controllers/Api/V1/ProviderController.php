<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function eligible(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_type'   => ['required', 'string'],
            'city_code'    => ['required', 'string', 'size:2'],
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
            ->whereHas('providerCoverage', fn ($q) => $q->where('city_code', $data['city_code']))
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

    /**
     * Enriched ranking context for AI-driven matching (n8n → Claude).
     * Includes couple context, city name joins, adjacency classification,
     * and richer provider attributes (business_description, portfolio, stats).
     */
    public function rankingCandidates(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_type'        => ['required', 'string'],
            'city_code'         => ['required', 'string', 'size:2'],
            'exclude_ids'         => ['nullable'],
            'limit'               => ['nullable', 'integer', 'min:1', 'max:50'],
            'service_request_uid' => ['nullable', 'string'],
        ]);

        // exclude_ids can arrive as array (eligible-style) OR comma-separated string (n8n-friendly)
        $exclude = $data['exclude_ids'] ?? [];
        if (is_string($exclude)) {
            $exclude = array_filter(array_map('trim', explode(',', $exclude)));
        }
        $exclude = array_map('intval', (array) $exclude);

        $limit = (int) ($data['limit'] ?? 20);
        $cityCode = (string) $data['city_code'];
        $serviceType = (string) $data['service_type'];

        $candidatesQuery = User::role('provider')
            ->where('users.status', 'active')
            ->whereHas('providerProfile', fn ($q) => $q->where('verified', true)->whereNull('suspended_at'))
            ->whereHas('providerServices', fn ($q) => $q->where('service_type', $serviceType))
            ->whereHas('providerCoverage', fn ($q) => $q->where('city_code', $cityCode))
            ->with(['providerProfile', 'providerServices', 'providerCoverage'])
            ->leftJoin('provider_profiles', 'provider_profiles.user_id', '=', 'users.id')
            ->select('users.*')
            ->orderByDesc('provider_profiles.verified')
            ->orderByDesc('provider_profiles.acceptance_rate')
            ->orderByDesc('provider_profiles.rating_avg');

        if (! empty($exclude)) {
            $candidatesQuery->whereNotIn('users.id', $exclude);
        }

        if (! empty($data['service_request_uid'])) {
            $candidatesQuery->where('users.id', '!=', (int) (\App\Models\ServiceRequest::where('service_uid', $data['service_request_uid'])->value('unlocked_by_user_id') ?: 0));
        }

        $candidates = $candidatesQuery->limit($limit)->get();

        $cityIndex = City::whereIn('code', $candidates->pluck('providerProfile.primary_city_code')->push($cityCode)->filter()->unique())
            ->get()->keyBy('code');

        $adjacency = (array) config('city-adjacency', []);
        $neighbours = $adjacency[$cityCode] ?? [];

        $context = [];
        if (! empty($data['service_request_uid'])) {
            $sr = \App\Models\ServiceRequest::where('service_uid', $data['service_request_uid'])->with('wedRequest')->first();
            if ($sr && $sr->wedRequest) {
                $wr = $sr->wedRequest;
                $context = [
                    'couple_district' => $wr->couple_district,
                    'wedding_date'   => $wr->wedding_date?->toDateString(),
                    'guest_count'    => $wr->guest_count?->value,
                    'budget_range'   => $wr->budget_range?->value,
                    'description'    => $wr->description,
                ];
            }
        }

        $payload = array_merge([
            // How many providers n8n should put in a round (set in /admin/settings).
            'providers_per_round' => (int) config('wedding.matching.providers_per_round', 3),
            'service_type' => $serviceType,
            'city_code'  => $cityCode,
            'city_name'  => $cityIndex->get($cityCode)?->name,
        ], $context, [
            'candidates' => $candidates->map(function (User $u) use ($cityCode, $neighbours, $cityIndex) {
                $p = $u->providerProfile;
                $primary = is_string($p?->primary_city_code) ? $p->primary_city_code : (string) ($p?->getAttributes()['primary_city_code'] ?? '');

                $distance = $primary === $cityCode
                    ? 'same_city'
                    : (in_array($primary, $neighbours, true) ? 'adjacent' : 'far');

                return [
                    'id'                   => $u->id,
                    'business_name'        => $p?->business_name,
                    'business_description' => $p?->business_description,
                    'price_tier'           => $p?->price_tier,
                    'min_price'        => $p?->min_price,
                    'max_price'        => $p?->max_price,
                    'years_experience'     => $p?->years_experience,
                    'languages'            => (array) ($p?->languages ?? []),
                    'rating_avg'           => (float) ($p?->rating_avg ?? 0),
                    'rating_count'         => (int) ($p?->rating_count ?? 0),
                    'acceptance_rate'      => (float) ($p?->acceptance_rate ?? 0),
                    'leads_accepted'       => (int) ($p?->leads_accepted ?? 0),
                    'portfolio_url'        => $p?->portfolio_url,
                    'portfolio_images'     => (array) ($p?->portfolio_images ?? []),
                    'primary_city_code'  => $primary,
                    'primary_city_name'  => $cityIndex->get($primary)?->name,
                    'coverage_cities'     => $u->providerCoverage->pluck('city_code')->map(fn ($v) => (string) $v)->values()->all(),
                    'distance_from_couple' => $distance,
                ];
            })->values(),
        ]);

        return ApiResponse::success($payload);
    }
}
