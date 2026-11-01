<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Enums\RequestStatus;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Demandes acceptées" tab + privacy display anonymization: couple contact
 * details are masked retention_days (14 by default) after acceptance.
 */
class AcceptedLeadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CitySeeder::class);
        $this->seed(RolesSeeder::class);
    }

    private function makeProvider(): User
    {
        $user = User::factory()->create([
            'email'  => 'p' . fake()->unique()->numberBetween(1000, 9999) . '@example.com',
            'role'   => 'provider',
            'status' => 'active',
        ]);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_city_code' => 'NY',
            'verified'            => true,
            'verified_at'         => now()->subDay(),
        ]);
        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'city_code' => 'NY', 'travel_fee' => 0]);

        return $user;
    }

    private function makeUnlockedLead(User $provider, \DateTimeInterface $unlockedAt): ServiceRequest
    {
        $wr = WedRequest::factory()->create([
            'couple_first_name'  => 'Khadidja',
            'couple_whatsapp'    => '+12125554567',
            'couple_city_code' => 'NY',
            'services_needed'    => ['photo'],
        ]);

        return ServiceRequest::create([
            'wed_request_id'      => $wr->id,
            'service_type'        => 'photo',
            'status'              => RequestStatus::Accepted->value,
            'current_round'       => 1,
            'unlocked_by_user_id' => $provider->id,
            'unlocked_at'         => $unlockedAt,
            'credits_charged'     => 1,
        ]);
    }

    public function test_fresh_lead_shows_contact_and_anonymization_notice(): void
    {
        $provider = $this->makeProvider();
        $lead = $this->makeUnlockedLead($provider, now()->subDays(2));

        $response = $this->actingAs($provider)->get(route('provider.leads.show', $lead->service_uid));

        $response->assertOk()
            ->assertSee('Khadidja')
            ->assertSee('+12125554567')
            ->assertSee('Coordonnées affichées jusqu'); // end-of-access notice
    }

    public function test_old_lead_is_anonymized(): void
    {
        $provider = $this->makeProvider();
        $lead = $this->makeUnlockedLead($provider, now()->subDays(15)); // > 14 j

        $response = $this->actingAs($provider)->get(route('provider.leads.show', $lead->service_uid));

        $response->assertOk()
            ->assertSee('Non disponible')
            ->assertSee('Coordonnées masquées')
            ->assertDontSee('Khadidja')
            ->assertDontSee('+12125554567');
    }

    public function test_accepted_tab_lists_only_own_leads_with_masking(): void
    {
        $provider = $this->makeProvider();
        $other = $this->makeProvider();

        $fresh = $this->makeUnlockedLead($provider, now()->subDays(1));
        $old = $this->makeUnlockedLead($provider, now()->subDays(20));
        $foreign = $this->makeUnlockedLead($other, now()->subDays(1));

        $response = $this->actingAs($provider)->get(route('provider.leads.accepted'));

        $response->assertOk()
            ->assertSee($fresh->service_type?->label())
            ->assertSee('Non disponible')          // the old one is masked in the list
            ->assertSee('Khadidja');          // the fresh one still shows the name

        // The other provider's lead must not leak into this list.
        $this->assertStringNotContainsString(
            $foreign->service_uid,
            $response->getContent(),
        );
    }

    public function test_model_anonymization_boundary(): void
    {
        $provider = $this->makeProvider();

        $justBefore = $this->makeUnlockedLead($provider, now()->subDays(14)->addMinutes(5));
        $justAfter = $this->makeUnlockedLead($provider, now()->subDays(14)->subMinutes(5));

        $this->assertFalse($justBefore->isAnonymized());
        $this->assertTrue($justAfter->isAnonymized());
    }
}
