<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\UnlockPack;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'unlock_pack_id' => UnlockPack::factory(),
            'amount_dzd'     => 7650,
            'unlocks_count'  => 6,
            'status'         => 'pending',
            'gateway'        => 'chargily',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status'         => 'failed',
            'failed_at'      => now(),
            'failure_reason' => 'gateway_failed',
        ]);
    }
}
