<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BudgetRange;
use App\Enums\GuestCount;
use App\Enums\RequestStatus;
use App\Enums\ServiceType;
use App\Enums\Wilaya;
use App\Models\WedRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WedRequest>
 */
class WedRequestFactory extends Factory
{
    protected $model = WedRequest::class;

    protected static array $algerianFirstNames = [
        'Mohamed', 'Ahmed', 'Yacine', 'Karim', 'Ryad', 'Bilal', 'Nassim', 'Anis', 'Adel', 'Walid',
        'Fatima', 'Amina', 'Nour', 'Sara', 'Imane', 'Lina', 'Yasmine', 'Sonia', 'Meriem', 'Hanane',
        'Mehdi', 'Sofiane', 'Reda', 'Hamza', 'Islam', 'Abderrahmane', 'Feriel', 'Khadija', 'Rania', 'Salma',
    ];

    public function definition(): array
    {
        $allServices = collect(ServiceType::cases())->map(fn ($s) => $s->value)->all();
        $count = $this->faker->numberBetween(1, 4);
        $services = $this->faker->randomElements($allServices, $count);

        $weddingDate = $this->faker->optional(0.85)->dateTimeBetween('+1 month', '+12 months');

        return [
            'status'               => RequestStatus::New->value,
            'couple_first_name'    => $this->faker->randomElement(self::$algerianFirstNames),
            'couple_whatsapp'      => '+213' . $this->faker->numerify('#########'),
            'couple_wilaya_code'   => $this->faker->randomElement(Wilaya::cases())->value,
            'couple_commune'       => $this->faker->optional()->city(),
            'wedding_date'         => $weddingDate,
            'wedding_season'       => $this->faker->randomElement(['Printemps', 'Été', 'Automne', 'Hiver']),
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
