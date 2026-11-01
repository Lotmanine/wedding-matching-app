<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UnlockPackSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restores the public demo to its initial state. Keeps what connects the
 * application to its integrations: cities, webhook endpoints, API tokens and
 * the service account that owns them.
 */
class ResetDemo extends Command
{
    protected $signature = 'wedding:demo-reset';

    protected $description = 'Erase visitor activity and restore the demo data (demo mode only)';

    private const TABLES = [
        'lead_disputes', 'service_requests', 'wed_requests', 'credit_ledger', 'purchases', 'feedback',
        'whatsapp_messages', 'webhook_deliveries', 'provider_services', 'provider_coverage', 'provider_profiles',
    ];

    public function handle(): int
    {
        if (! config('wedding.demo.enabled')) {
            $this->error('Demo mode is off: nothing was changed.');

            return self::FAILURE;
        }

        Schema::disableForeignKeyConstraints();
        foreach (self::TABLES as $table) {
            DB::table($table)->truncate();
        }
        Schema::enableForeignKeyConstraints();

        User::where('role', UserRole::Provider->value)
            ->whereDoesntHave('tokens')
            ->get()
            ->each(fn (User $user) => $user->forceDelete());

        $this->call('db:seed', ['--class' => SettingsSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => UnlockPackSeeder::class, '--force' => true]);
        $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
        $this->call('wedding:cleanup-orphan-uploads');

        $this->info('Demo data restored.');

        return self::SUCCESS;
    }
}
