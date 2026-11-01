<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    public function test_update_persists_settings_and_clears_cache(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        Cache::put('settings:all', 'stale', 3600);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'credits_unlock_cost'            => 2,
            'matching_round_timeout_minutes' => 180,
            'matching_providers_per_round'   => 4,
            'privacy_retention_days'           => 21,
        ])->assertRedirect();

        $this->assertSame('2',   Setting::where('key', 'credits.unlock_cost')->value('value'));
        $this->assertSame('180', Setting::where('key', 'matching.round_timeout_minutes')->value('value'));

        $this->assertSame(2, Setting::get('credits.unlock_cost'));
    }

    public function test_feedback_alert_email_can_be_saved_cleared_and_is_validated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $numbers = [
            'credits_unlock_cost'            => 1,
            'matching_round_timeout_minutes' => 120,
            'matching_providers_per_round'   => 3,
            'privacy_retention_days'           => 14,
        ];

        $this->actingAs($admin)->put(route('admin.settings.update'), $numbers + ['feedback_alert_email' => 'alerts@example.com'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('alerts@example.com', Setting::get('feedback.alert_email'));

        $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk()->assertSee('alerts@example.com');

        $this->actingAs($admin)->put(route('admin.settings.update'), $numbers + ['feedback_alert_email' => 'not-an-email'])
            ->assertSessionHasErrors('feedback_alert_email');
        $this->assertSame('alerts@example.com', Setting::get('feedback.alert_email'));

        $this->actingAs($admin)->put(route('admin.settings.update'), $numbers + ['feedback_alert_email' => ''])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('', Setting::get('feedback.alert_email'));
    }

    public function test_saved_settings_actually_drive_the_application(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'credits_unlock_cost'            => 2,
            'matching_round_timeout_minutes' => 180,
            'matching_providers_per_round'   => 4,
            'privacy_retention_days'           => 21,
        ])->assertRedirect();

        $this->assertSame(2, config('wedding.credits.unlock_cost'));
        $this->assertSame(180, config('wedding.matching.round_timeout_minutes'));
        $this->assertSame(4, config('wedding.matching.providers_per_round'));
        $this->assertSame(21, config('wedding.privacy.retention_days'));
        $this->assertSame(180, \App\Services\Matching\RoundStateMachine::roundTimeoutMinutes());

        // A new request is valid for the configured number of days.
        $this->seed(\Database\Seeders\CitySeeder::class);
        $wr = \App\Models\WedRequest::factory()->create(['couple_city_code' => 'NY']);
        $this->assertEqualsWithDelta(21, now()->diffInDays($wr->expires_at), 0.01);
    }

    public function test_settings_get_falls_back_to_default(): void
    {
        $this->assertSame('def', Setting::get('does.not.exist', 'def'));
    }
}
