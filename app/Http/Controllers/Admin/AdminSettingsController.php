<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Support\SettingsConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(): View
    {
        $settings = Setting::orderBy('key')->get()->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $mapping = [
            'credits_unlock_cost'            => 'credits.unlock_cost',
            'matching_round_timeout_minutes' => 'matching.round_timeout_minutes',
            'matching_providers_per_round'   => 'matching.providers_per_round',
            'anpdp_retention_days'           => 'anpdp.retention_days',
        ];

        $validated = $request->validate([
            'credits_unlock_cost'            => ['required', 'integer', 'min:1'],
            'matching_round_timeout_minutes' => ['required', 'integer', 'min:10'],
            'matching_providers_per_round'   => ['required', 'integer', 'min:1', 'max:10'],
            'anpdp_retention_days'           => ['required', 'integer', 'min:1', 'max:365'],
            'feedback_alert_email'           => ['nullable', 'email', 'max:255'],
        ]);

        foreach ($mapping as $flat => $key) {
            Setting::set($key, (string) $validated[$flat], 'int');
        }

        // Address emailed each time a provider sends feedback. Empty = no email.
        if ($request->exists('feedback_alert_email')) {
            Setting::set('feedback.alert_email', (string) ($validated['feedback_alert_email'] ?? ''), 'string');
        }

        SettingsConfig::apply();

        $this->audit->log('settings.update', null, $validated);

        return back()->with('status', __('admin.settings.saved_ok'));
    }
}
