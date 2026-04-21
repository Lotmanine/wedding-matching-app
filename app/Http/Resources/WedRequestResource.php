<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WedRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'wed_uid'            => $this->wed_uid,
            'status'             => $this->status?->value ?? (string) $this->status,
            'couple' => [
                'first_name'   => $this->couple_first_name,
                'whatsapp'     => $this->couple_whatsapp,
                'wilaya_code'  => (string) $this->couple_wilaya_code,
                'commune'      => $this->couple_commune,
            ],
            'wedding' => [
                'date'          => $this->wedding_date?->toDateString(),
                'season'        => $this->wedding_season,
                'guest_count'   => $this->guest_count?->value ?? (string) $this->guest_count,
                'budget_range'  => $this->budget_range?->value ?? (string) $this->budget_range,
            ],
            'services_needed'    => (array) ($this->services_needed ?? []),
            'description'        => $this->description,
            'service_requests'   => ServiceRequestResource::collection(
                $this->whenLoaded('serviceRequests')
            ),
            'expires_at'         => $this->expires_at?->toAtomString(),
            'created_at'         => $this->created_at?->toAtomString(),
        ];
    }
}
