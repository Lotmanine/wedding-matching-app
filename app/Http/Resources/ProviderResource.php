<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray($request): array
    {
        $profile = $this->providerProfile;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'business_name'     => $profile?->business_name,
            'primary_city_code' => $profile ? (string) $profile->primary_city_code : null,
            'price_tier'        => $profile?->price_tier,
            'min_price'     => $profile?->min_price,
            'max_price'     => $profile?->max_price,
            'rating' => [
                'average' => (float) ($profile?->rating_avg ?? 0),
                'count'   => (int) ($profile?->rating_count ?? 0),
            ],
            'stats' => [
                'leads_received'  => (int) ($profile?->leads_received ?? 0),
                'leads_accepted'  => (int) ($profile?->leads_accepted ?? 0),
                'acceptance_rate' => (float) ($profile?->acceptance_rate ?? 0),
            ],
            'portfolio_url'     => $profile?->portfolio_url,
            'portfolio_images'  => (array) ($profile?->portfolio_images ?? []),
            'languages'         => (array) ($profile?->languages ?? []),
            'verified'          => (bool) ($profile?->verified ?? false),
            'services'          => $this->whenLoaded('providerServices', fn () =>
                $this->providerServices->pluck('service_type')->map(fn ($v) => $v instanceof \App\Enums\ServiceType ? $v->value : (string) $v)->values()
            ),
            'coverage'          => $this->whenLoaded('providerCoverage', fn () =>
                $this->providerCoverage->map(fn ($c) => [
                    'city_code' => (string) $c->city_code,
                    'travel_fee' => (int) $c->travel_fee,
                ])->values()
            ),
        ];
    }
}
