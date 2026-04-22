<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Commune;
use App\Models\UnlockPack;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Models\Wilaya;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SmokeTest extends Command
{
    protected $signature = 'zawaj:smoke-test {--send-email=}';

    protected $description = 'Run critical-path health checks. Exit 0 if all pass, 1 if any fail.';

    private int $failed = 0;

    public function handle(): int
    {
        $this->check('DB connection', fn () => DB::connection()->getPdo() !== null);
        $this->check('Users table readable', fn () => DB::table('users')->count() >= 0);
        $this->check('Cache read/write', function () {
            Cache::put('__smoke', 'ok', 10);

            return Cache::get('__smoke') === 'ok';
        });
        $this->check('Storage public disk writable', function () {
            Storage::disk('public')->put('__smoke.txt', 'ok');
            $ok = Storage::disk('public')->get('__smoke.txt') === 'ok';
            Storage::disk('public')->delete('__smoke.txt');

            return $ok;
        });
        $this->check('APP_KEY set (32+ chars)', fn () => strlen((string) config('app.key')) >= 32);
        $this->check('Admin user exists', fn () => User::role('admin')->exists());
        $this->check('Wilayas seeded (69)', fn () => Wilaya::count() === 69);
        $this->check('Communes seeded (~1541)', fn () => Commune::count() >= 1540);
        $this->check('Unlock packs seeded (3)', fn () => UnlockPack::count() >= 3);

        if (app()->environment('production')) {
            $this->check('APP_DEBUG=false in production', fn () => config('app.debug') === false);
        }

        $this->check('Webhook endpoints reachable', function () {
            foreach (WebhookEndpoint::where('active', true)->get() as $endpoint) {
                $response = Http::timeout(5)->connectTimeout(5)->get($endpoint->url);
                if ($response->status() >= 500) {
                    return false;
                }
            }

            return true;
        });

        if ($email = (string) $this->option('send-email')) {
            $this->check('SMTP test email', function () use ($email) {
                \Illuminate\Support\Facades\Mail::raw('Zawaj Pro smoke test — if you got this, SMTP is working.', fn ($m) => $m->to($email)->subject('Zawaj Pro smoke test'));

                return true;
            });
        }

        if ($this->failed > 0) {
            $this->error("Smoke test FAILED — {$this->failed} check(s) failed.");

            return self::FAILURE;
        }

        $this->info('All smoke-test checks passed.');

        return self::SUCCESS;
    }

    private function check(string $name, callable $fn): void
    {
        try {
            $ok = (bool) $fn();
        } catch (\Throwable $e) {
            $ok = false;
            $this->line("<fg=red>✗</> {$name} — {$e->getMessage()}");
            $this->failed++;

            return;
        }

        if ($ok) {
            $this->line("<fg=green>✓</> {$name}");
        } else {
            $this->line("<fg=red>✗</> {$name}");
            $this->failed++;
        }
    }
}
