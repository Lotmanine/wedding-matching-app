<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'credits.unlock_cost',             'value' => '1',    'type' => 'int'],
            ['key' => 'matching.round_timeout_minutes',  'value' => '120',  'type' => 'int'],
            ['key' => 'matching.providers_per_round',    'value' => '3',    'type' => 'int'],
            ['key' => 'matching.max_rounds',             'value' => '3',    'type' => 'int'],
            ['key' => 'privacy.retention_days',          'value' => '14',   'type' => 'int'],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(['key' => $row['key']], $row);
        }

        Cache::forget('settings:all');
    }
}
