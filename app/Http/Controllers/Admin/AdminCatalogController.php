<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ServiceType;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\City;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Platform catalog: which cities and which services are open on the
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
            'cities'        => City::orderBy('code')->get(),
            'services'       => ServiceType::cases(),
            'activeServices' => ServiceType::activeValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $allCities = City::pluck('code')->all();
        $allServices = array_column(ServiceType::cases(), 'value');

        $validated = $request->validate([
            'active_cities'    => ['required', 'array', 'min:1'],
            'active_cities.*'  => ['string', 'in:' . implode(',', $allCities)],
            'active_services'   => ['required', 'array', 'min:1'],
            'active_services.*' => ['string', 'in:' . implode(',', $allServices)],
        ], [
            'active_cities.required'  => 'Au moins une ville doit rester active.',
            'active_services.required' => 'Au moins un service doit rester actif.',
        ]);

        $activeCities = array_values(array_unique($validated['active_cities']));
        $activeServices = array_values(array_unique($validated['active_services']));

        City::whereIn('code', $activeCities)->update(['active' => true]);
        City::whereNotIn('code', $activeCities)->update(['active' => false]);

        $disabledServices = array_values(array_diff($allServices, $activeServices));
        Setting::set('catalog.disabled_services', json_encode($disabledServices), 'json');

        $this->audit->log('catalog.update', null, [
            'cities_active'    => count($activeCities),
            'cities_inactive'  => count($allCities) - count($activeCities),
            'services_disabled' => $disabledServices,
        ]);

        return back()->with('status', 'Catalogue mis à jour.');
    }
}
