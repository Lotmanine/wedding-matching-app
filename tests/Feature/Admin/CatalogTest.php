<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ServiceType;
use App\Livewire\CoupleRequestForm;
use App\Models\Setting;
use App\Models\User;
use App\Models\WedRequest;
use App\Models\City;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Admin catalog: activating/deactivating cities and services, and the
 * effect on new couple submissions.
 */
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_deactivates_city_and_service(): void
    {
        $admin = $this->makeAdmin();

        $allCities = City::pluck('code')->all();
        $activeCities = array_values(array_diff($allCities, ['LA'])); // drop Los Angeles
        $activeServices = array_values(array_diff(
            array_column(ServiceType::cases(), 'value'),
            ['beauty'],
        ));

        $this->actingAs($admin)
            ->put(route('admin.catalog.update'), [
                'active_cities'  => $activeCities,
                'active_services' => $activeServices,
            ])
            ->assertSessionHas('status');

        $this->assertFalse((bool) City::where('code', 'LA')->value('active'));
        $this->assertTrue((bool) City::where('code', 'NY')->value('active'));
        $this->assertNotContains('beauty', ServiceType::activeValues());
        $this->assertContains('photo', ServiceType::activeValues());
    }

    public function test_catalog_requires_at_least_one_of_each(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.catalog.update'), [
                'active_cities'  => [],
                'active_services' => ['photo'],
            ])
            ->assertSessionHasErrors('active_cities');
    }

    public function test_couple_form_rejects_deactivated_city_and_service(): void
    {
        City::where('code', 'LA')->update(['active' => false]);
        Setting::set('catalog.disabled_services', json_encode(['beauty']), 'json');

        // Deactivated city fails step 2 validation.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+12125554567')
            ->set('couple_city_code', 'LA')
            ->set('step', 2)
            ->call('nextStep')
            ->assertHasErrors('couple_city_code');

        // Deactivated service fails submission.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+12125554567')
            ->set('couple_city_code', 'NY')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_20k_to_35k')
            ->set('services_needed', ['beauty'])
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasErrors('services_needed.0');

        $this->assertSame(0, WedRequest::count());

        // Active combination still passes.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+12125554567')
            ->set('couple_city_code', 'NY')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_20k_to_35k')
            ->set('services_needed', ['photo'])
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertSame(1, WedRequest::count());
    }

    public function test_manager_cannot_access_catalog(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'status' => 'active']);
        $manager->assignRole('manager');

        $this->actingAs($manager)->get(route('admin.catalog.edit'))->assertForbidden();
    }
}
