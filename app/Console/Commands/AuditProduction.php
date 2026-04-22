<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AuditProduction extends Command
{
    protected $signature = 'zawaj:audit-production';

    protected $description = 'Verify the application config is production-safe.';

    private int $issues = 0;

    public function handle(): int
    {
        $this->assert('APP_ENV=production',  config('app.env') === 'production');
        $this->assert('APP_DEBUG=false',     config('app.debug') === false);
        $this->assert('APP_URL uses https',  str_starts_with((string) config('app.url'), 'https://'));
        $this->assert('SESSION_SECURE_COOKIE=true', (bool) config('session.secure') === true);
        $this->assert('APP_KEY set (32+ chars)',    strlen((string) config('app.key')) >= 32);
        $this->assert('Queue not sync',      config('queue.default') !== 'sync');
        $this->assert('CHARGILY_MODE=live',  config('zawaj.chargily.mode') === 'live');
        $this->assert('Chargily secret set', ! empty(config('zawaj.chargily.secret_key')));
        $this->assert('Chargily webhook secret set', ! empty(config('zawaj.chargily.webhook_secret')));

        if ($this->issues > 0) {
            $this->error("{$this->issues} production-readiness issue(s).");

            return self::FAILURE;
        }

        $this->info('Production config looks good.');

        return self::SUCCESS;
    }

    private function assert(string $name, bool $ok): void
    {
        if ($ok) {
            $this->line("<fg=green>✓</> {$name}");
        } else {
            $this->line("<fg=red>✗</> {$name}");
            $this->issues++;
        }
    }
}
