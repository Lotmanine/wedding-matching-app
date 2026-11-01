<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'wedding:make-admin {email}';

    protected $description = 'Promote an existing user to admin (assigns Spatie role + sets role column).';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user with email {$email}.");

            return self::FAILURE;
        }

        $user->forceFill(['role' => UserRole::Admin->value, 'status' => 'active'])->save();
        $user->syncRoles(['admin']);

        $this->info("Promoted {$email} to admin.");

        return self::SUCCESS;
    }
}
