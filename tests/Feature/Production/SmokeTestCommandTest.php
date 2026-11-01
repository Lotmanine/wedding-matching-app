<?php

declare(strict_types=1);

namespace Tests\Feature\Production;

use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UnlockPackSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_smoke_test_succeeds_when_seeded(): void
    {
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->seed(UnlockPackSeeder::class);

        // Need at least one admin for the check
        $admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->artisan('wedding:smoke-test')->assertExitCode(0);
    }

    public function test_smoke_test_fails_without_seed(): void
    {
        $this->artisan('wedding:smoke-test')->assertExitCode(1);
    }
}
