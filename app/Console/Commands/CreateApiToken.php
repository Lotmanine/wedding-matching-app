<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateApiToken extends Command
{
    protected $signature = 'wedding:create-api-token {--name=n8n-service} {--abilities=n8n-service} {--email=n8n@example.com}';

    protected $description = 'Create (or upsert) a service-account user and issue a Sanctum API token with the given abilities. Prints the plaintext token once.';

    public function handle(AuditLogger $audit): int
    {
        $name = (string) $this->option('name');
        $email = (string) $this->option('email');
        $abilities = array_filter(array_map('trim', explode(',', (string) $this->option('abilities'))));

        if (empty($abilities)) {
            $this->error('--abilities must not be empty');

            return self::FAILURE;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => "Service: {$name}",
                'password'          => Hash::make(bin2hex(random_bytes(32))),
                'role'              => UserRole::Admin->value,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );
        $user->syncRoles(['admin']);

        $tokenResult = $user->createToken($name, $abilities);
        $plainText = $tokenResult->plainTextToken;

        $audit->log('api.token.created', $user, [
            'token_name' => $name,
            'abilities'  => $abilities,
            'token_id'   => $tokenResult->accessToken->id,
        ]);

        $this->info('Token created. Copy the plaintext token NOW — it is not recoverable:');
        $this->line('');
        $this->line($plainText);
        $this->line('');
        $this->line("Abilities: " . implode(', ', $abilities));
        $this->line("User:      {$email}");

        return self::SUCCESS;
    }
}
