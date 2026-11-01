<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'wedding:test-email {to}';

    protected $description = 'Send a test email to verify SMTP configuration.';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        Mail::raw(
            "Wedding Matching App — email test\n\nSent at " . now()->toDateTimeString() . " from " . config('app.url'),
            fn ($m) => $m->to($to)->subject('[Wedding Matching App] Test email'),
        );

        $this->info("Test email sent to {$to} via " . config('mail.default'));

        return self::SUCCESS;
    }
}
