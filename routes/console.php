<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Backup\Console\Commands\Backup;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wedding:cleanup-expired')
    ->dailyAt('03:00')
    ->onOneServer();

// Public demo: back to a clean state once a day.
Schedule::command('wedding:demo-reset')
    ->dailyAt('04:00')
    ->when(fn () => config('wedding.demo.enabled'))
    ->onOneServer();

Schedule::command('wedding:advance-rounds')
    ->everyFiveMinutes()
    ->onOneServer();

Schedule::command('wedding:cleanup-orphan-uploads')
    ->hourly()
    ->onOneServer();

// Database + storage backups (spatie/laravel-backup). Scheduling activates
// automatically once the package is installed (`composer require spatie/laravel-backup`),
// so this is safe to ship before the package is present.
if (class_exists(Backup::class)) {
    Schedule::command('backup:clean')
        ->dailyAt('01:00')->onOneServer();
    Schedule::command('backup:run')
        ->dailyAt('01:30')->onOneServer();
    Schedule::command('backup:monitor')
        ->dailyAt('02:00')->onOneServer();
}
