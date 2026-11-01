<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        // Never ship a hardcoded password to production. Use ADMIN_PASSWORD when
        // provided; otherwise generate a strong random one and surface it once so
        // the operator can log in and rotate it immediately.
        $password = env('ADMIN_PASSWORD');
        $generated = false;
        if (blank($password)) {
            if (app()->environment('production')) {
                $password = Str::password(20);
                $generated = true;
            } else {
                $password = 'password'; // local/testing convenience only
            }
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Wedding Matching Admin'),
                'password' => Hash::make($password),
                'role' => UserRole::Admin->value,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles(['admin']);

        if ($generated) {
            $this->command?->warn('=================================================================');
            $this->command?->warn("Admin créé : {$email}");
            $this->command?->warn("Mot de passe temporaire : {$password}");
            $this->command?->warn('Connectez-vous et changez-le immédiatement. Ce mot de passe ne sera plus affiché.');
            $this->command?->warn('=================================================================');
        }
    }
}
