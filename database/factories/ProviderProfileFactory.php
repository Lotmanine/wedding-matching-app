<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    protected $model = ProviderProfile::class;

    protected static array $businessNames = [
        'Golden Hour Studio', 'Harvest Table Catering', 'The Rose Pavilion', 'Evergreen Weddings',
        'Silver Frame Photo', 'Bloom & Vine Decor', 'Sweet Layers Bakery', 'Sound of the City DJ',
        'Glow Bridal Beauty', 'Ever After Events', 'Horizon Studio', 'Fine Plate Catering',
        'Garden Terrace Venue', 'Timeless Memories Photo', 'Elegant Touch Decor',
        'Royal Table Catering', 'Lumen Studio', 'Grand Wedding Co', 'The Lakeside Hall', 'Pearl Photo',
    ];

    public function definition(): array
    {
        $priceTier = $this->faker->randomElement(['budget', 'mid', 'premium', 'luxury']);
        [$min, $max] = match ($priceTier) {
            'budget'  => [500, 2000],
            'mid'     => [2000, 5000],
            'premium' => [5000, 15000],
            'luxury'  => [15000, 40000],
        };

        return [
            'user_id'              => User::factory(),
            'business_name'        => $this->faker->randomElement(self::$businessNames) . ' ' . $this->faker->numerify('##'),
            'business_description' => $this->faker->sentence(15),
            'business_type'        => $this->faker->randomElement(['sole_proprietor', 'llc', 'corporation', 'individual']),
            'trade_register'       => $this->faker->optional()->numerify('BR-########'),
            'primary_city_code'  => City::inRandomOrder()->value('code') ?? 'NY',
            'price_tier'           => $priceTier,
            'min_price'            => $min,
            'max_price'            => $max,
            'years_experience'     => $this->faker->numberBetween(1, 20),
            'portfolio_url'        => $this->faker->optional()->url(),
            'portfolio_images'     => null,
            'logo_url'             => null,
            'languages'            => $this->faker->randomElements(['en', 'fr', 'es'], $this->faker->numberBetween(1, 3)),
            'verified'             => $this->faker->boolean(40),
            'verified_at'          => null,
            'rating_avg'           => $this->faker->randomFloat(2, 3.0, 5.0),
            'rating_count'         => $this->faker->numberBetween(0, 80),
            'leads_received'       => $this->faker->numberBetween(0, 50),
            'leads_accepted'       => 0,
            'leads_refused'        => 0,
            'acceptance_rate'      => 0,
            'credit_balance'       => $this->faker->numberBetween(0, 30),
            'onboarding_completed' => true,
        ];
    }
}
