<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 'manager' = day-to-day back-office ops (demandes, round-robin,
        // providers, purchases) without money-config / settings / user admin.
        foreach (['admin', 'manager', 'provider', 'couple'] as $name) {
            Role::findOrCreate($name, 'web');
        }
    }
}
