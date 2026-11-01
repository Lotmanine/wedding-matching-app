<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CitySeeder::class,
            RolesSeeder::class,
            SettingsSeeder::class,
            UnlockPackSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (config('wedding.demo.enabled')) {
            $this->call(DemoSeeder::class);
        } elseif (app()->environment('local')) {
            $this->call(DevDataSeeder::class);
        }
    }
}
