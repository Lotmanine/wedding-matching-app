<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

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
