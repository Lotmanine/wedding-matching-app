<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayaSeeder::class,
            UnlockPackSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DevDataSeeder::class);
        }
    }
}
