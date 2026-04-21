<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        return [
            'wed_request_id' => WedRequest::factory(),
            'service_type'   => $this->faker->randomElement(ServiceType::cases())->value,
            'status'         => RequestStatus::New->value,
            'current_round'  => 0,
        ];
    }
}
