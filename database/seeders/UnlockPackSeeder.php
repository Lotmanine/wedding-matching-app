<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UnlockPack;
use Illuminate\Database\Seeder;

class UnlockPackSeeder extends Seeder
{
    public function run(): void
    {
        $packs = [
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'unlocks_count' => 3,
                'price'         => 59,
                'is_popular'    => false,
                'sort_order'    => 1,
                'active'        => true,
            ],
            [
                'name'          => 'Essential',
                'slug'          => 'essential',
                'unlocks_count' => 6,
                'price'         => 99,
                'is_popular'    => true,
                'sort_order'    => 2,
                'active'        => true,
            ],
            [
                'name'          => 'Premium',
                'slug'          => 'premium',
                'unlocks_count' => 10,
                'price'         => 149,
                'is_popular'    => false,
                'sort_order'    => 3,
                'active'        => true,
            ],
        ];

        foreach ($packs as $pack) {
            UnlockPack::updateOrCreate(['slug' => $pack['slug']], $pack);
        }
    }
}
