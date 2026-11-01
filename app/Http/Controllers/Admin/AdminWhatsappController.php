<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\Whatsapp\WhatsappSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin configuration of the Meta WhatsApp Cloud API integration:
 *  - on/off switch (n8n skips sending while off or while keys are missing)
 *  - phone number ID, access token, verify token, app secret.
 *
 * The values live in the generic settings table (whatsapp.* keys) via
 * WhatsappSettings and are fetched by the n8n `wedding-whatsapp` workflow.
 */
class AdminWhatsappController extends Controller
{
    public function __construct(
        private readonly WhatsappSettings $whatsapp,
        private readonly AuditLogger $audit,
    ) {
    }

    public function edit(): View
    {
        return view('admin.whatsapp.edit', [
            'switchedOn'    => $this->whatsapp->switchedOn(),
            'canSend'       => $this->whatsapp->canSend(),
            'isActive'      => $this->whatsapp->isActive(),
            'phoneNumberId' => $this->whatsapp->phoneNumberId(),
            'secrets'       => $this->whatsapp->secretStatus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled'         => ['nullable', 'boolean'],
            'phone_number_id' => ['nullable', 'string', 'regex:/^\d{5,30}$/'],
            'access_token'    => ['nullable', 'string', 'max:1000'],
            'verify_token'    => ['nullable', 'string', 'max:255'],
            'app_secret'      => ['nullable', 'string', 'max:255'],
        ], [
            'phone_number_id.regex' => __('The Phone number ID is a numeric ID (not the phone number).'),
        ]);

        $this->whatsapp->save($validated);

        // Never log secret values — only which fields changed.
        $this->audit->log('whatsapp.settings.update', null, [
            'enabled'         => (bool) ($validated['enabled'] ?? false),
            'phone_number_id' => $validated['phone_number_id'] ?? null,
            'access_token'    => filled($validated['access_token'] ?? null) ? 'updated' : 'unchanged',
            'verify_token'    => filled($validated['verify_token'] ?? null) ? 'updated' : 'unchanged',
            'app_secret'      => filled($validated['app_secret'] ?? null) ? 'updated' : 'unchanged',
        ]);

        return back()->with('status', __('WhatsApp configuration saved.'));
    }
}
