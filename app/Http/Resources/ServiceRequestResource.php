<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'service_uid'               => $this->service_uid,
            'wed_request_uid'           => $this->whenLoaded('wedRequest', fn () => $this->wedRequest?->wed_uid),
            'wed_request'               => $this->whenLoaded('wedRequest', fn () => new WedRequestResource($this->wedRequest)),
            'service_type'              => $this->service_type?->value ?? (string) $this->service_type,
            'status'                    => $this->status?->value ?? (string) $this->status,
            'current_round'             => (int) $this->current_round,
            'round_started_at'          => $this->round_started_at?->toAtomString(),
            'round_1_provider_ids'      => (array) ($this->round_1_provider_ids ?? []),
            'round_2_provider_ids'      => (array) ($this->round_2_provider_ids ?? []),
            'round_3_provider_ids'      => (array) ($this->round_3_provider_ids ?? []),
            'all_notified_provider_ids' => (array) ($this->all_notified_provider_ids ?? []),
            'refused_by_provider_ids'   => (array) ($this->refused_by_provider_ids ?? []),
            'unlocked_by_user_id'       => $this->unlocked_by_user_id,
            'unlocked_at'               => $this->unlocked_at?->toAtomString(),
            'unlocked_via'              => $this->unlocked_via,
            'credits_charged'           => (int) $this->credits_charged,
            'expires_at'                => $this->expires_at?->toAtomString(),
            'created_at'                => $this->created_at?->toAtomString(),
        ];
    }
}
