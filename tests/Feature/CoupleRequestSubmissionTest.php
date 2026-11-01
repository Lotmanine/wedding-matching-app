<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Livewire\CoupleRequestForm;
use App\Models\ServiceRequest;
use App\Models\WedRequest;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class CoupleRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WilayaSeeder::class);
        RateLimiter::clear('couple-form:127.0.0.1');
    }

    public function test_form_renders_at_demande_route(): void
    {
        $response = $this->get('/demande');

        $response->assertOk();
        $response->assertSeeLivewire(CoupleRequestForm::class);
    }

    public function test_landing_page_renders_with_cta(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Zawaj Pro');
        $response->assertSee(route('couple.request.create'), false);
    }

    public function test_submission_stores_the_ip_address_anonymised(): void
    {
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_1m_to_2m')
            ->set('services_needed', ['photo'])
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit');

        // The test client connects from 127.0.0.1: the last byte must be masked.
        $this->assertSame('127.0.0.0', WedRequest::firstOrFail()->ip_address);
    }

    public function test_confirmation_page_tells_the_couple_what_really_happens(): void
    {
        $wr = WedRequest::factory()->create(['couple_wilaya_code' => '16']);
        ServiceRequest::create(['wed_request_id' => $wr->id, 'service_type' => 'photo', 'status' => 'accepted', 'current_round' => 1]);
        ServiceRequest::create(['wed_request_id' => $wr->id, 'service_type' => 'traiteur', 'status' => 'round1', 'current_round' => 1]);
        ServiceRequest::create(['wed_request_id' => $wr->id, 'service_type' => 'machta', 'status' => 'open_marketplace', 'current_round' => 3]);

        $this->get(route('couple.request.confirmation', ['wedUid' => $wr->wed_uid]))
            ->assertOk()
            ->assertSee('Demande acceptée')
            ->assertSee('Recherche en cours')
            ->assertSee('Recherche élargie')
            ->assertSee('Chacun a 2 heures pour répondre', false);
    }

    public function test_public_pages_render_without_invented_figures(): void
    {
        foreach (['/', '/devenir-prestataire', '/conditions-utilisation', '/confidentialite'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('2 000+')
                ->assertDontSee('69 wilayas')
                ->assertDontSee('Témoignages');
        }

        $this->get('/')->assertSee('Disponible à');
    }

    public function test_successful_submission_creates_wed_request_with_service_requests(): void
    {
        $this->assertSame(0, WedRequest::count());

        $component = Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('couple_commune', 'Alger Centre')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('guest_count', 'from_200_to_300')
            ->set('budget_range', 'from_1m_to_2m')
            ->set('services_needed', ['photo', 'traiteur', 'salle'])
            ->set('description', 'Mariage traditionnel.')
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit');

        $this->assertSame(1, WedRequest::count());
        $wr = WedRequest::first();
        $this->assertSame('Amina', $wr->couple_first_name);
        $this->assertSame('+213661234567', $wr->couple_whatsapp);
        $this->assertSame(RequestStatus::New, $wr->status);
        $this->assertNotEmpty($wr->wed_uid);
        $this->assertSame(3, $wr->serviceRequests()->count());
        $this->assertSame(3, ServiceRequest::count());

        $component->assertRedirect(route('couple.request.confirmation', ['wedUid' => $wr->wed_uid]));
    }

    public function test_invalid_whatsapp_format_is_rejected(): void
    {
        Livewire::test(CoupleRequestForm::class)
            ->set('step', 1)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '0661234567') // no +213 prefix
            ->call('nextStep')
            ->assertHasErrors(['couple_whatsapp' => 'regex']);

        $this->assertSame(0, WedRequest::count());
    }

    public function test_missing_terms_consent_is_rejected_on_submit(): void
    {
        Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('guest_count', 'from_100_to_200')
            ->set('budget_range', 'from_500k_to_1m')
            ->set('services_needed', ['photo'])
            ->set('acceptTerms', false)
            ->set('step', 5)
            ->call('submit')
            ->assertHasErrors(['acceptTerms' => 'accepted']);

        $this->assertSame(0, WedRequest::count());
    }

    public function test_rate_limit_blocks_fourth_submission_from_same_ip(): void
    {
        $payload = fn () => Livewire::test(CoupleRequestForm::class)
            ->set('couple_first_name', 'Amina')
            ->set('couple_whatsapp', '+213661234567')
            ->set('couple_wilaya_code', '16')
            ->set('wedding_date', now()->addMonths(6)->toDateString())
            ->set('budget_range', 'from_500k_to_1m')
            ->set('services_needed', ['photo'])
            ->set('acceptTerms', true)
            ->set('step', 5)
            ->call('submit');

        for ($i = 0; $i < 3; $i++) {
            $payload();
        }

        $this->assertSame(3, WedRequest::count());

        $component = $payload();
        $component->assertHasErrors('submission');

        $this->assertSame(3, WedRequest::count(), '4th attempt should be blocked by rate limiter');
    }

    public function test_cleanup_erases_personal_data_of_expired_requests(): void
    {
        $fresh = WedRequest::factory()->create(['couple_first_name' => 'Lina']);

        $expired = WedRequest::factory()->create(['couple_first_name' => 'Amina', 'couple_whatsapp' => '+213661234567', 'description' => 'Texte libre']);
        $expired->forceFill(['expires_at' => now()->subDay(), 'ip_address' => '10.0.0.0', 'user_agent' => 'UA'])->save();

        // Accepted 3 days ago: the provider's 14-day access window is still open.
        $stillInUse = WedRequest::factory()->create(['couple_first_name' => 'Sara']);
        $stillInUse->forceFill(['expires_at' => now()->subDay()])->save();
        ServiceRequest::factory()->create(['wed_request_id' => $stillInUse->id, 'service_type' => 'photo'])
            ->forceFill(['unlocked_at' => now()->subDays(3)])->save();

        Artisan::call('zawaj:cleanup-expired');

        $row = WedRequest::withTrashed()->find($expired->id);
        $this->assertSame('Supprimé', $row->couple_first_name);
        $this->assertSame('', $row->couple_whatsapp);
        $this->assertNull($row->description);
        $this->assertNull($row->ip_address);
        $this->assertNull($row->user_agent);
        $this->assertNotNull($row->personal_data_purged_at);

        $this->assertSame('Lina', WedRequest::find($fresh->id)->couple_first_name);
        $this->assertSame('Sara', WedRequest::withTrashed()->find($stillInUse->id)->couple_first_name);

        // Once the provider's window has closed, the next run erases it too.
        $this->travel(12)->days();
        Artisan::call('zawaj:cleanup-expired');
        $this->assertSame('Supprimé', WedRequest::withTrashed()->find($stillInUse->id)->couple_first_name);
    }

    public function test_cleanup_expired_command_soft_deletes_past_requests(): void
    {
        $fresh = WedRequest::factory()->create();

        $expired = WedRequest::factory()->create();
        $expired->forceFill(['expires_at' => now()->subDay()])->save();

        ServiceRequest::factory()->create([
            'wed_request_id' => $expired->id,
            'service_type'   => 'photo',
        ]);

        Artisan::call('zawaj:cleanup-expired');

        $this->assertNotNull(WedRequest::withTrashed()->find($expired->id)->deleted_at);
        $this->assertNull(WedRequest::find($fresh->id)->deleted_at);

        $this->assertSame(0, ServiceRequest::where('wed_request_id', $expired->id)->count());
        $this->assertSame(1, ServiceRequest::withTrashed()->where('wed_request_id', $expired->id)->count());

        $this->assertSame(
            RequestStatus::Expired,
            WedRequest::withTrashed()->find($expired->id)->status
        );
    }
}
