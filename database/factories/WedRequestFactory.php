<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Models\District;
use App\Models\City;
use App\Models\WedRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WedRequest>
 */
class WedRequestFactory extends Factory
{
    protected $model = WedRequest::class;

    protected static array $firstNames = [
        'James', 'Michael', 'Daniel', 'Lucas', 'Ethan', 'Noah', 'Oliver', 'Hugo', 'Thomas', 'Louis',
        'Emma', 'Olivia', 'Sophia', 'Grace', 'Chloe', 'Lily', 'Camille', 'Alice', 'Julia', 'Claire',
        'Nathan', 'Adam', 'Leo', 'Paul', 'Victor', 'Charlotte', 'Amelia', 'Ella', 'Laura', 'Anna',
    ];

    public function definition(): array
    {
        $allServices = collect(ServiceType::cases())->map(fn ($s) => $s->value)->all();
        $count = $this->faker->numberBetween(1, 4);
        $services = $this->faker->randomElements($allServices, $count);

        $weddingDate = $this->faker->optional(0.85)->dateTimeBetween('+1 month', '+12 months');

        $city = City::inRandomOrder()->first();
        $district = $city
            ? District::forCity($city->id)->inRandomOrder()->first()
            : null;

        return [
            'status'               => RequestStatus::New->value,
            'couple_first_name'    => $this->faker->randomElement(self::$firstNames),
            'couple_whatsapp'      => '+1212555' . $this->faker->numerify('####'),
            'couple_city_code'   => $city?->code ?? 'NY',
            'couple_district'       => $district?->name,
            'wedding_date'         => $weddingDate,
            'wedding_season'       => $this->faker->randomElement(WedRequest::SEASONS),
            'guest_count'          => $this->faker->randomElement(GuestCount::cases())->value,
            'budget_range'         => $this->faker->randomElement(BudgetRange::cases())->value,
            'description'          => $this->faker->optional()->sentence(12),
            'services_needed'      => $services,
            'budget_warning_shown' => false,
            'source'               => 'form',
            'ip_address'           => $this->faker->ipv4(),
            'user_agent'           => $this->faker->userAgent(),
        ];
    }
}
