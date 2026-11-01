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
            'price'         => $this->faker->numberBetween(29, 199),
            'is_popular'    => false,
            'sort_order'    => 0,
            'active'        => true,
        ];
    }

    public function starter(): static
    {
        return $this->state(fn () => [
            'name'          => 'Starter',
            'slug'          => 'starter',
            'unlocks_count' => 3,
            'price'         => 59,
            'is_popular'    => false,
            'sort_order'    => 1,
            'active'        => true,
        ]);
    }

    public function essential(): static
    {
        return $this->state(fn () => [
            'name'          => 'Essential',
            'slug'          => 'essential',
            'unlocks_count' => 6,
            'price'         => 99,
            'is_popular'    => true,
            'sort_order'    => 2,
            'active'        => true,
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn () => [
            'name'          => 'Premium',
            'slug'          => 'premium',
            'unlocks_count' => 10,
            'price'         => 149,
            'is_popular'    => false,
            'sort_order'    => 3,
            'active'        => true,
        ]);
    }
}
