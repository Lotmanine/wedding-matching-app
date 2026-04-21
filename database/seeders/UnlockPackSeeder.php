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
                'name'          => 'Pack Découverte',
                'slug'          => 'pack-decouverte',
                'unlocks_count' => 3,
                'price_dzd'     => 4500,
                'is_popular'    => false,
                'sort_order'    => 1,
                'active'        => true,
            ],
            [
                'name'          => 'Pack Essentiel',
                'slug'          => 'pack-essentiel',
                'unlocks_count' => 6,
                'price_dzd'     => 7650,
                'is_popular'    => true,
                'sort_order'    => 2,
                'active'        => true,
            ],
            [
                'name'          => 'Pack Premium',
                'slug'          => 'pack-premium',
                'unlocks_count' => 10,
                'price_dzd'     => 11250,
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
