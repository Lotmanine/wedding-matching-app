<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Wilaya;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Platform catalog: which wilayas and which services are open on the
 * platform. Deactivating removes them from NEW selections everywhere
 * (couple form, provider registration, filters) — existing data keeps
 * working untouched.
 */
class AdminCatalogController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function edit(): View
    {
        return view('admin.catalog.edit', [
            'wilayas'        => Wilaya::orderBy('code')->get(),
            'services'       => ServiceType::cases(),
            'activeServices' => ServiceType::activeValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $allWilayas = Wilaya::pluck('code')->all();
        $allServices = array_column(ServiceType::cases(), 'value');

        $validated = $request->validate([
            'active_wilayas'    => ['required', 'array', 'min:1'],
            'active_wilayas.*'  => ['string', 'in:' . implode(',', $allWilayas)],
            'active_services'   => ['required', 'array', 'min:1'],
            'active_services.*' => ['string', 'in:' . implode(',', $allServices)],
        ], [
            'active_wilayas.required'  => 'Au moins une wilaya doit rester active.',
            'active_services.required' => 'Au moins un service doit rester actif.',
        ]);

        $activeWilayas = array_values(array_unique($validated['active_wilayas']));
        $activeServices = array_values(array_unique($validated['active_services']));

        Wilaya::whereIn('code', $activeWilayas)->update(['active' => true]);
        Wilaya::whereNotIn('code', $activeWilayas)->update(['active' => false]);

        $disabledServices = array_values(array_diff($allServices, $activeServices));
        Setting::set('catalog.disabled_services', json_encode($disabledServices), 'json');

        $this->audit->log('catalog.update', null, [
            'wilayas_active'    => count($activeWilayas),
            'wilayas_inactive'  => count($allWilayas) - count($activeWilayas),
            'services_disabled' => $disabledServices,
        ]);

        return back()->with('status', 'Catalogue mis à jour.');
    }
}
