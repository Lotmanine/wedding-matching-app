<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ServiceType;
use App\Livewire\CoupleRequestForm;
use App\Models\Setting;
use App\Models\User;
use App\Models\WedRequest;
use App\Models\Wilaya;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Admin catalog: activating/deactivating wilayas and services, and the
 * effect on new couple submissions.
 */
class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_admin_deactivates_wilaya_and_service(): void
    {
        $admin = $this->makeAdmin();

        $allWilayas = Wilaya::pluck('code')->all();
        $activeWilayas = array_values(array_diff($allWilayas, ['31'])); // drop Oran
        $activeServices = array_values(array_diff(
            array_column(ServiceType::cases(), 'value'),
            ['machta'],
        ));

        $this->actingAs($admin)
            ->put(route('admin.catalog.update'), [
                'active_wilayas'  => $activeWilayas,
                'active_services' => $activeServices,
            ])
            ->assertSessionHas('status');

        $this->assertFalse((bool) Wilaya::where('code', '31')->value('active'));
        $this->assertTrue((bool) Wilaya::where('code', '16')->value('active'));
        $this->assertNotContains('machta', ServiceType::activeValues());
        $this->assertContains('photo', ServiceType::activeValues());
    }

    public function test_catalog_requires_at_least_one_of_each(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put(route('admin.catalog.update'), [
                'active_wilayas'  => [],
                'active_services' => ['photo'],
            ])
            ->assertSessionHasErrors('active_wilayas');
    }

    public function test_couple_form_rejects_deactivated_wilaya_and_service(): void
    {
        Wilaya::where('code', '31')->update(['active' => false]);
        Setting::set('catalog.disabled_services', json_encode(['machta']), 'json');

        // Deactivated wilaya fails step 2 validation.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '31')
            ->set('step', 2)
            ->call('nextStep')
            ->assertHasErrors('couple_wilaya_code');

        // Deactivated service fails submission.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_1m_to_2m')
            ->set('services_needed', ['machta'])
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit')
            ->assertHasErrors('services_needed.0');

        $this->assertSame(0, WedRequest::count());

        // Active combination still passes.
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_1m_to_2m')
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
