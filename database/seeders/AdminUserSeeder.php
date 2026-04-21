<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@zawajpro.dz'],
            [
                'name'              => 'Admin Zawaj Pro',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin->value,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['admin']);
    }
}
