<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Spatie\Backup\Console\Commands\Backup;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('zawaj:cleanup-expired')
    ->dailyAt('03:00')
    ->timezone('Africa/Algiers')
    ->onOneServer();

Schedule::command('zawaj:advance-rounds')
    ->everyFiveMinutes()
    ->timezone('Africa/Algiers')
    ->onOneServer();

Schedule::command('zawaj:cleanup-orphan-uploads')
    ->hourly()
    ->timezone('Africa/Algiers')
    ->onOneServer();

// Database + storage backups (spatie/laravel-backup). Scheduling activates
// automatically once the package is installed (`composer require spatie/laravel-backup`),
// so this is safe to ship before the package is present.
if (class_exists(Backup::class)) {
    Schedule::command('backup:clean')
        ->dailyAt('01:00')->timezone('Africa/Algiers')->onOneServer();
    Schedule::command('backup:run')
        ->dailyAt('01:30')->timezone('Africa/Algiers')->onOneServer();
    Schedule::command('backup:monitor')
        ->dailyAt('02:00')->timezone('Africa/Algiers')->onOneServer();
}
