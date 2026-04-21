<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Livewire\Provider\RegistrationForm;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WilayaSeeder::class);
        $this->seed(RolesSeeder::class);
        Storage::fake('public');
    }

    public function test_register_page_renders(): void
    {
        $this->get('/provider/register')
            ->assertOk()
            ->assertSeeLivewire(RegistrationForm::class);
    }

    public function test_successful_registration_creates_user_profile_role_pivots(): void
    {
        Event::fake();

        Livewire::test(RegistrationForm::class)
            ->set('email', 'new@provider.dz')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Karim Ben')
            ->set('phone', '+213661234567')
            ->set('whatsapp', '+213661234567')
            ->set('accept_terms', true)
            ->set('accept_anpdp', true)
            ->set('business_name', 'Studio Karim')
            ->set('business_type', 'auto-entrepreneur')
            ->set('business_description', 'Photographe mariage Alger.')
            ->set('primary_wilaya_code', '16')
            ->set('price_tier', 'mid')
            ->set('languages', ['fr', 'ar'])
            ->set('services', ['photo', 'video'])
            ->set('coverage_wilayas', ['16', '09'])
            ->set('travel_fees', ['09' => 3000, '16' => 0])
            ->set('portfolio_urls', ['https://instagram.com/studio.karim'])
            ->set('logo_upload', UploadedFile::fake()->create('logo.png', 200, 'image/png'))
            ->set('portfolio_images', [
                UploadedFile::fake()->create('one.jpg', 300, 'image/jpeg'),
                UploadedFile::fake()->create('two.jpg', 300, 'image/jpeg'),
            ])
            ->call('submit')
            ->assertRedirect(route('provider.dashboard'));

        $user = User::where('email', 'new@provider.dz')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('provider'));
        $this->assertSame('pending_verification', $user->status);

        $profile = ProviderProfile::where('user_id', $user->id)->first();
        $this->assertNotNull($profile);
        $this->assertSame('Studio Karim', $profile->business_name);
        $this->assertSame(2, count($profile->portfolio_images ?? []));
        $this->assertNotNull($profile->logo_url);

        $this->assertSame(2, ProviderService::where('user_id', $user->id)->count());
        $this->assertSame(2, ProviderCoverage::where('user_id', $user->id)->count());
        $primary = ProviderCoverage::where(['user_id' => $user->id, 'wilaya_code' => '16'])->first();
        $this->assertSame(0, (int) $primary->travel_fee_dzd);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'dup@provider.dz']);

        Livewire::test(RegistrationForm::class)
            ->set('email', 'dup@provider.dz')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Test')
            ->set('phone', '+213661234567')
            ->set('whatsapp', '+213661234567')
            ->set('accept_terms', true)
            ->set('accept_anpdp', true)
            ->call('nextStep')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_invalid_phone_regex_rejected(): void
    {
        Livewire::test(RegistrationForm::class)
            ->set('email', 'fresh@provider.dz')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Test')
            ->set('phone', '0661234567') // no +213 prefix
            ->set('whatsapp', '+213661234567')
            ->set('accept_terms', true)
            ->set('accept_anpdp', true)
            ->call('nextStep')
            ->assertHasErrors(['phone' => 'regex']);
    }

    public function test_step_requires_at_least_one_service(): void
    {
        Livewire::test(RegistrationForm::class)
            ->set('step', 3)
            ->set('services', [])
            ->call('nextStep')
            ->assertHasErrors(['services' => 'required']);
    }
}
