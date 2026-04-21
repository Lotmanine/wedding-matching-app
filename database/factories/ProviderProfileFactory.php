<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Wilaya;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    protected $model = ProviderProfile::class;

    protected static array $businessNames = [
        'Studio Al Ward', 'Traiteur El Baraka', 'Salle Le Palais Rose', 'Mariage Prestige DZ',
        'Photo Rêve d\'Or', 'Décoration Nour', 'Pâtisserie Dar El Halwa', 'DJ Sound of Alger',
        'Machta Amina Beauty', 'Événement El Farah', 'Studio Horizon', 'Traiteur Delices d\'Algerie',
        'Salle Jardins d\'Andalousie', 'Photo Memoire Eternelle', 'Decor Elegance',
        'Le Royal Traiteur', 'Studio Lumiere', 'Mariage Royal', 'Salle Andalouse', 'Photo Perle',
    ];

    public function definition(): array
    {
        $priceTier = $this->faker->randomElement(['budget', 'mid', 'premium', 'luxury']);
        [$min, $max] = match ($priceTier) {
            'budget'  => [30000, 120000],
            'mid'     => [120000, 400000],
            'premium' => [400000, 900000],
            'luxury'  => [900000, 2500000],
        };

        return [
            'user_id'              => User::factory(),
            'business_name'        => $this->faker->randomElement(self::$businessNames) . ' ' . $this->faker->numerify('##'),
            'business_description' => $this->faker->sentence(15),
            'business_type'        => $this->faker->randomElement(['auto-entrepreneur', 'sarl', 'eurl', 'particulier']),
            'trade_register'       => $this->faker->optional()->numerify('RC-##-######-A'),
            'primary_wilaya_code'  => Wilaya::inRandomOrder()->value('code') ?? '16',
            'price_tier'           => $priceTier,
            'min_price'            => $min,
            'max_price'            => $max,
            'years_experience'     => $this->faker->numberBetween(1, 20),
            'portfolio_url'        => $this->faker->optional()->url(),
            'portfolio_images'     => null,
            'logo_url'             => null,
            'languages'            => $this->faker->randomElements(['fr', 'ar', 'ber', 'en'], $this->faker->numberBetween(1, 3)),
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
