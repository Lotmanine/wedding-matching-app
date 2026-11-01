<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Livewire\Provider\RegistrationForm;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
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
        $this->seed(CitySeeder::class);
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
            ->set('email', 'new@provider.example.com')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Karim Ben')
            ->set('phone', '+12125554567')
            ->set('whatsapp', '+12125554567')
            ->set('accept_terms', true)
            ->set('accept_privacy', true)
            ->set('business_name', 'Studio Karim')
            ->set('business_type', 'sole_proprietor')
            ->set('business_description', 'Photographe mariage New York.')
            ->set('primary_city_code', 'NY')
            ->set('price_tier', 'mid')
            ->set('languages', ['fr', 'en'])
            ->set('services', ['photo', 'video'])
            ->set('coverage_cities', ['NY', 'MI'])
            ->set('travel_fees', ['MI' => 3000, 'NY' => 0])
            ->set('portfolio_urls', ['https://instagram.com/studio.karim'])
            ->set('logo_upload', UploadedFile::fake()->create('logo.png', 200, 'image/png'))
            ->set('portfolio_images', [
                UploadedFile::fake()->create('one.jpg', 300, 'image/jpeg'),
                UploadedFile::fake()->create('two.jpg', 300, 'image/jpeg'),
            ])
            ->call('submit')
            ->assertRedirect(route('provider.dashboard'));

        $user = User::where('email', 'new@provider.example.com')->first();
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
        $primary = ProviderCoverage::where(['user_id' => $user->id, 'city_code' => 'NY'])->first();
        $this->assertSame(0, (int) $primary->travel_fee);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'dup@provider.example.com']);

        Livewire::test(RegistrationForm::class)
            ->set('email', 'dup@provider.example.com')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Test')
            ->set('phone', '+12125554567')
            ->set('whatsapp', '+12125554567')
            ->set('accept_terms', true)
            ->set('accept_privacy', true)
            ->call('nextStep')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_invalid_phone_regex_rejected(): void
    {
        Livewire::test(RegistrationForm::class)
            ->set('email', 'fresh@provider.example.com')
            ->set('password', 'secretpassword')
            ->set('password_confirmation', 'secretpassword')
            ->set('full_name', 'Test')
            ->set('phone', '0661234567') // no country prefix
            ->set('whatsapp', '+12125554567')
            ->set('accept_terms', true)
            ->set('accept_privacy', true)
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
