<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UnlockPack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UnlockPack>
 */
class UnlockPackFactory extends Factory
{
    protected $model = UnlockPack::class;

    public function definition(): array
    {
        $name = 'Pack ' . $this->faker->word();

        return [
            'name'          => $name,
            'slug'          => Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'unlocks_count' => $this->faker->numberBetween(1, 20),
            'price_dzd'     => $this->faker->numberBetween(1000, 20000),
            'is_popular'    => false,
            'sort_order'    => 0,
            'active'        => true,
        ];
    }

    public function decouverte(): static
    {
        return $this->state(fn () => [
            'name'          => 'Pack Découverte',
            'slug'          => 'pack-decouverte',
            'unlocks_count' => 3,
            'price_dzd'     => 4500,
            'is_popular'    => false,
            'sort_order'    => 1,
            'active'        => true,
        ]);
    }

    public function essentiel(): static
    {
        return $this->state(fn () => [
            'name'          => 'Pack Essentiel',
            'slug'          => 'pack-essentiel',
            'unlocks_count' => 6,
            'price_dzd'     => 7650,
            'is_popular'    => true,
            'sort_order'    => 2,
            'active'        => true,
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn () => [
            'name'          => 'Pack Premium',
            'slug'          => 'pack-premium',
            'unlocks_count' => 10,
            'price_dzd'     => 11250,
            'is_popular'    => false,
            'sort_order'    => 3,
            'active'        => true,
        ]);
    }
}
