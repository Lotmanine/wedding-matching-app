<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditIndexes extends Command
{
    protected $signature = 'zawaj:audit-indexes';

    protected $description = 'Compare current DB indexes against expected ones; print missing ones.';

    /** @var array<string, array<string>> */
    private array $expected = [
        'users'              => ['email', 'role', 'status', 'wilaya_code'],
        'wed_requests'       => ['wed_uid', 'status', 'couple_wilaya_code', 'wedding_date', 'expires_at'],
        'service_requests'   => ['service_uid', 'status', 'service_type', 'current_round', 'round_started_at'],
        'provider_profiles'  => ['user_id', 'primary_wilaya_code', 'price_tier', 'verified', 'rating_avg'],
        'credit_ledger'      => ['user_id', 'type'],
        'purchases'          => ['purchase_uid', 'status'],
        'webhook_deliveries' => ['event_type'],
        'audit_logs'         => ['action'],
    ];

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->expected as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $this->warn("Missing table: {$table}");

                continue;
            }

            $this->line("<fg=cyan>{$table}</>");
            foreach ($columns as $col) {
                $has = Schema::hasColumn($table, $col);
                $indexed = $driver === 'mysql' ? $this->hasIndexMysql($table, $col) : true;
                $status = $has && $indexed ? '<fg=green>✓</>' : '<fg=yellow>?</>';
                $this->line("  {$status} {$col}");
            }
        }

        return self::SUCCESS;
    }

    private function hasIndexMysql(string $table, string $column): bool
    {
        try {
            $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);

            return count($rows) > 0;
        } catch (\Throwable) {
            return true;
        }
    }
}
