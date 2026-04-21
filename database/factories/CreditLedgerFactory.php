<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CreditLedger;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditLedger>
 */
class CreditLedgerFactory extends Factory
{
    protected $model = CreditLedger::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['purchase', 'unlock', 'refund', 'bonus', 'admin_adjustment']);
        $delta = match ($type) {
            'purchase'         => $this->faker->randomElement([3, 6, 10]),
            'unlock'           => -1,
            'refund'           => 1,
            'bonus'            => $this->faker->numberBetween(1, 3),
            'admin_adjustment' => $this->faker->numberBetween(-5, 5),
        };

        return [
            'user_id'            => User::factory(),
            'delta'              => $delta,
            'balance_after'      => $this->faker->numberBetween(0, 50),
            'type'               => $type,
            'reference_type'     => null,
            'reference_id'       => null,
            'notes'              => $this->faker->optional()->sentence(),
            'created_by_user_id' => null,
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn () => ['type' => 'purchase', 'delta' => 6]);
    }

    public function unlock(): static
    {
        return $this->state(fn () => ['type' => 'unlock', 'delta' => -1]);
    }

    public function bonus(): static
    {
        return $this->state(fn () => ['type' => 'bonus', 'delta' => 3]);
    }
}
