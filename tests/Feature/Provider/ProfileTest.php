<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Livewire\Provider\ProfileEditor;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
        Storage::fake('public');
    }

    private function makeProvider(): User
    {
        $user = User::factory()->create([
            'email'       => 'pro@zawajpro.dz',
            'role'        => 'provider',
            'wilaya_code' => '16',
            'phone'       => '+213661234567',
            'whatsapp'    => '+213661234567',
        ]);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_wilaya_code' => '16',
            'languages'           => ['fr'],
            'business_type'       => 'auto-entrepreneur',
            'price_tier'          => 'mid',
            'portfolio_images'    => [],
        ]);

        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $user;
    }

    public function test_unauthenticated_redirects_to_provider_login(): void
    {
        $this->get('/provider/profile')->assertRedirect();
    }

    public function test_couple_role_forbidden_from_provider_routes(): void
    {
        $user = User::factory()->create(['role' => 'couple']);
        $user->assignRole('couple');

        $this->actingAs($user)->get('/provider/dashboard')->assertForbidden();
    }

    public function test_profile_edit_loads_current_values(): void
    {
        $user = $this->makeProvider();

        Livewire::actingAs($user)
            ->test(ProfileEditor::class)
            ->assertSet('primary_wilaya_code', '16')
            ->assertSet('services', ['photo']);
    }

    public function test_profile_update_persists_services_and_coverage(): void
    {
        $user = $this->makeProvider();

        Livewire::actingAs($user)
            ->test(ProfileEditor::class)
            ->set('business_name', 'Refreshed Studio')
            ->set('services', ['photo', 'video', 'traiteur'])
            ->set('coverage_wilayas', ['16', '09', '31'])
            ->set('travel_fees', ['16' => 0, '09' => 2000, '31' => 5000])
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh()->load(['providerProfile', 'providerServices', 'providerCoverage']);
        $this->assertSame('Refreshed Studio', $user->providerProfile->business_name);
        $this->assertSame(3, $user->providerServices->count());
        $this->assertSame(3, $user->providerCoverage->count());
        $cov9 = ProviderCoverage::where(['user_id' => $user->id, 'wilaya_code' => '09'])->first();
        $this->assertSame(2000, (int) $cov9->travel_fee_dzd);
    }

    public function test_profile_update_handles_logo_upload(): void
    {
        $user = $this->makeProvider();

        Livewire::actingAs($user)
            ->test(ProfileEditor::class)
            ->set('logo_new', UploadedFile::fake()->create('new-logo.png', 150, 'image/png'))
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->providerProfile->logo_url);
    }
}
