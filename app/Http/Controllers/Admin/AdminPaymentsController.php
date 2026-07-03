<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\Payments\PaymentSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super-admin configuration of payment methods:
 *  - toggle online (Chargily) and offline (versement CCP/banque)
 *  - edit the bank / CCP coordinates shown to providers on the offline
 *    checkout page.
 *
 * The values live in the generic settings table (payments.* keys) via
 * PaymentSettings, so no env changes are needed.
 */
class AdminPaymentsController extends Controller
{
    public function __construct(
        private readonly PaymentSettings $payments,
        private readonly AuditLogger $audit,
    ) {
    }

    public function edit(): View
    {
        return view('admin.payments.edit', [
            'onlineEnabled'  => $this->payments->onlineEnabled(),
            'offlineEnabled' => $this->payments->offlineEnabled(),
            'details'        => $this->payments->offlineDetails(),
            'chargilyMode'   => $this->payments->chargilyMode(),
            'chargilyKeys'   => $this->payments->chargilyKeyStatus(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'online_enabled'          => ['nullable', 'boolean'],
            'offline_enabled'         => ['nullable', 'boolean'],
            'bank_name'               => ['nullable', 'string', 'max:120'],
            'account_holder'          => ['nullable', 'string', 'max:120'],
            'rib'                     => ['nullable', 'string', 'max:60'],
            'ccp_number'              => ['nullable', 'string', 'max:60'],
            'ccp_key'                 => ['nullable', 'string', 'max:10'],
            'instructions'            => ['nullable', 'string', 'max:500'],
            'chargily_mode'           => ['nullable', 'in:test,live'],
            'chargily_secret_key'     => ['nullable', 'string', 'max:255'],
            'chargily_webhook_secret' => ['nullable', 'string', 'max:255'],
        ]);

        $this->payments->save($validated);
        $this->payments->saveChargily($validated);

        // Never log secret values — only which fields changed.
        $this->audit->log('payments.settings.update', null, [
            'online_enabled'          => (bool) ($validated['online_enabled'] ?? false),
            'offline_enabled'         => (bool) ($validated['offline_enabled'] ?? false),
            'chargily_mode'           => $validated['chargily_mode'] ?? null,
            'chargily_secret_key'     => filled($validated['chargily_secret_key'] ?? null) ? 'updated' : 'unchanged',
            'chargily_webhook_secret' => filled($validated['chargily_webhook_secret'] ?? null) ? 'updated' : 'unchanged',
        ]);

        return back()->with('status', 'Modes de paiement enregistrés.');
    }
}
