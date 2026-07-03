<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Models\LeadDispute;
use App\Models\ProviderCoverage;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Database\Seeders\RolesSeeder;
use Database\Seeders\WilayaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression + feature coverage for the admin-ops batch: credit-adjust cast,
 * verification status sync, manager role boundaries, admin user creation,
 * full demande edit with child sync, and the credit-back (dispute) flow.
 */
class AdminOpsTest extends TestCase
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

    private function makeManager(): User
    {
        $manager = User::factory()->create(['role' => 'manager', 'status' => 'active']);
        $manager->assignRole('manager');

        return $manager;
    }

    private function makeProvider(bool $verified = true, int $credits = 0, string $status = 'active'): User
    {
        $user = User::factory()->create([
            'email'  => 'p' . fake()->unique()->numberBetween(1000, 9999) . '@zawajpro.dz',
            'role'   => 'provider',
            'status' => $status,
        ]);
        $user->assignRole('provider');

        ProviderProfile::factory()->create([
            'user_id'             => $user->id,
            'primary_wilaya_code' => '16',
            'verified'            => $verified,
            'verified_at'         => $verified ? now()->subDay() : null,
            'credit_balance'      => $credits,
        ]);
        ProviderService::create(['user_id' => $user->id, 'service_type' => 'photo']);
        ProviderCoverage::create(['user_id' => $user->id, 'wilaya_code' => '16', 'travel_fee_dzd' => 0]);

        return $user->fresh(['providerProfile']);
    }

    // ─── Fix regressions ─────────────────────────────────────

    public function test_admin_credit_adjust_accepts_form_string_amount(): void
    {
        $admin = $this->makeAdmin();
        $provider = $this->makeProvider(credits: 2);

        // Form input arrives as a string — previously blew up with a TypeError.
        $this->actingAs($admin)
            ->post(route('admin.providers.credits.adjust', $provider->id), [
                'amount' => '5',
                'reason' => 'Bonus de bienvenue',
            ])
            ->assertSessionHas('status');

        $this->assertSame(7, (int) $provider->providerProfile->fresh()->credit_balance);
    }

    public function test_verify_activates_pending_account(): void
    {
        $admin = $this->makeAdmin();
        $provider = $this->makeProvider(verified: false, status: 'pending_verification');

        $this->actingAs($admin)
            ->post(route('admin.providers.verify', $provider->id))
            ->assertRedirect();

        $provider->refresh();
        $this->assertTrue((bool) $provider->providerProfile->fresh()->verified);
        $this->assertSame('active', $provider->status, 'verify must also activate the account');
    }

    public function test_unverify_returns_account_to_pending(): void
    {
        $admin = $this->makeAdmin();
        $provider = $this->makeProvider(verified: true, status: 'active');

        $this->actingAs($admin)
            ->post(route('admin.providers.unverify', $provider->id))
            ->assertRedirect();

        $this->assertSame('pending_verification', $provider->fresh()->status);
    }

    // ─── Manager role boundaries ─────────────────────────────

    public function test_manager_reaches_ops_pages_but_not_admin_only(): void
    {
        $manager = $this->makeManager();

        $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('admin.providers.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.service_requests.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.disputes.index'))->assertOk();

        $this->actingAs($manager)->get(route('admin.settings.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.packs.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.payments.edit'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.users.index'))->assertForbidden();
    }

    // ─── Admin user creation ─────────────────────────────────

    public function test_admin_creates_manager_with_generated_password(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name'  => 'Ops Manager',
                'email' => 'manager@zawajpro.dz',
                'role'  => 'manager',
            ])
            ->assertRedirect();

        $user = User::where('email', 'manager@zawajpro.dz')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('manager'));
        $this->assertSame('active', $user->status);
    }

    public function test_admin_creates_provider_verified_and_active(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name'                => 'Studio Nour',
                'email'               => 'studio@zawajpro.dz',
                'role'                => 'provider',
                'business_name'       => 'Studio Nour Photography',
                'primary_wilaya_code' => '16',
            ])
            ->assertRedirect();

        $user = User::where('email', 'studio@zawajpro.dz')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('provider'));
        $this->assertSame('active', $user->status);
        $this->assertTrue((bool) $user->providerProfile->verified);
        $this->assertSame('Studio Nour Photography', $user->providerProfile->business_name);
    }

    // ─── Full demande edit with child sync ───────────────────

    public function test_demande_edit_syncs_child_service_requests(): void
    {
        $admin = $this->makeAdmin();
        $wr = WedRequest::factory()->create([
            'couple_wilaya_code' => '16',
            'services_needed'    => ['photo', 'traiteur'],
        ]);
        foreach (['photo', 'traiteur'] as $svc) {
            ServiceRequest::create([
                'wed_request_id' => $wr->id,
                'service_type'   => $svc,
                'status'         => RequestStatus::New->value,
                'current_round'  => 0,
            ]);
        }

        // Remove traiteur, add salle.
        $this->actingAs($admin)
            ->put(route('admin.requests.update', $wr->wed_uid), [
                'couple_first_name'  => $wr->couple_first_name,
                'couple_whatsapp'    => '+213661234567',
                'couple_wilaya_code' => '16',
                'status'             => 'new',
                'services_needed'    => ['photo', 'salle'],
            ])
            ->assertRedirect(route('admin.requests.show', $wr->wed_uid));

        $wr->refresh();
        $this->assertEqualsCanonicalizing(['photo', 'salle'], (array) $wr->services_needed);

        $types = ServiceRequest::where('wed_request_id', $wr->id)
            ->get()
            ->map(fn ($sr) => $sr->service_type?->value ?? (string) $sr->service_type);
        $this->assertContains('salle', $types, 'added service must create a child request');

        $traiteur = ServiceRequest::withTrashed()
            ->where('wed_request_id', $wr->id)
            ->get()
            ->first(fn ($sr) => ($sr->service_type?->value ?? '') === 'traiteur');
        $this->assertNotNull($traiteur->deleted_at, 'removed service child must be soft-deleted');
    }

    public function test_demande_edit_blocks_removing_accepted_service(): void
    {
        $admin = $this->makeAdmin();
        $provider = $this->makeProvider();
        $wr = WedRequest::factory()->create([
            'couple_wilaya_code' => '16',
            'services_needed'    => ['photo'],
        ]);
        ServiceRequest::create([
            'wed_request_id'      => $wr->id,
            'service_type'        => 'photo',
            'status'              => RequestStatus::Accepted->value,
            'current_round'       => 1,
            'unlocked_by_user_id' => $provider->id,
            'unlocked_at'         => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.requests.update', $wr->wed_uid), [
                'couple_first_name'  => $wr->couple_first_name,
                'couple_whatsapp'    => '+213661234567',
                'couple_wilaya_code' => '16',
                'status'             => 'accepted',
                'services_needed'    => ['salle'], // tries to drop accepted photo
            ])
            ->assertSessionHasErrors('services_needed');
    }

    public function test_admin_assigns_provider_with_explicit_round_from_form(): void
    {
        // Regression: the round select posts a STRING ("2"); the state machine
        // is strictly typed (?int) and blew up with a TypeError.
        $admin = $this->makeAdmin();
        $provider = $this->makeProvider();
        $wr = WedRequest::factory()->create(['couple_wilaya_code' => '16', 'services_needed' => ['photo']]);
        $sr = ServiceRequest::create([
            'wed_request_id' => $wr->id,
            'service_type'   => 'photo',
            'status'         => RequestStatus::New->value,
            'current_round'  => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.service_requests.assign', $sr->service_uid), [
                'provider_id' => (string) $provider->id,
                'round'       => '2',
            ])
            ->assertSessionHas('status');

        $sr->refresh();
        $this->assertSame(2, (int) $sr->current_round);
        $this->assertContains($provider->id, (array) $sr->round_2_provider_ids);
    }

    // ─── Credit-back (litiges) ───────────────────────────────

    private function makeUnlockedLead(User $provider): ServiceRequest
    {
        $wr = WedRequest::factory()->create([
            'couple_wilaya_code' => '16',
            'services_needed'    => ['photo'],
        ]);

        return ServiceRequest::create([
            'wed_request_id'      => $wr->id,
            'service_type'        => 'photo',
            'status'              => RequestStatus::Accepted->value,
            'current_round'       => 1,
            'unlocked_by_user_id' => $provider->id,
            'unlocked_at'         => now(),
            'credits_charged'     => 1,
        ]);
    }

    public function test_provider_disputes_lead_and_admin_refund_restores_credits(): void
    {
        $provider = $this->makeProvider(credits: 4);
        $lead = $this->makeUnlockedLead($provider);

        // Provider flags the lead.
        $this->actingAs($provider)
            ->post(route('provider.leads.dispute', $lead->service_uid), [
                'reason'  => 'wrong_number',
                'details' => 'Numéro hors service.',
            ])
            ->assertSessionHas('status');

        $dispute = LeadDispute::first();
        $this->assertNotNull($dispute);
        $this->assertSame('pending', $dispute->status);

        // Second flag on the same lead is refused.
        $this->actingAs($provider)
            ->post(route('provider.leads.dispute', $lead->service_uid), ['reason' => 'fake'])
            ->assertSessionHas('error');

        // Admin approves → credits refunded.
        $admin = $this->makeAdmin();
        $this->actingAs($admin)
            ->post(route('admin.disputes.approve', $dispute->id))
            ->assertSessionHas('status');

        $dispute->refresh();
        $this->assertSame('approved', $dispute->status);
        $this->assertNotNull($dispute->credit_ledger_id);
        $this->assertSame(5, (int) $provider->providerProfile->fresh()->credit_balance);

        // Double-approve is blocked (no double refund).
        $this->actingAs($admin)
            ->post(route('admin.disputes.approve', $dispute->id))
            ->assertSessionHas('error');
        $this->assertSame(5, (int) $provider->providerProfile->fresh()->credit_balance);
    }

    public function test_provider_cannot_dispute_someone_elses_lead(): void
    {
        $owner = $this->makeProvider();
        $other = $this->makeProvider();
        $lead = $this->makeUnlockedLead($owner);

        $this->actingAs($other)
            ->post(route('provider.leads.dispute', $lead->service_uid), ['reason' => 'fake'])
            ->assertForbidden();
    }

    public function test_admin_rejects_dispute_with_note(): void
    {
        $provider = $this->makeProvider(credits: 4);
        $lead = $this->makeUnlockedLead($provider);

        $this->actingAs($provider)
            ->post(route('provider.leads.dispute', $lead->service_uid), ['reason' => 'no_response']);

        $dispute = LeadDispute::first();
        $manager = $this->makeManager(); // managers handle litiges too

        $this->actingAs($manager)
            ->post(route('admin.disputes.reject', $dispute->id), [
                'admin_note' => 'Le couple confirme avoir été contacté.',
            ])
            ->assertSessionHas('status');

        $this->assertSame('rejected', $dispute->fresh()->status);
        $this->assertSame(4, (int) $provider->providerProfile->fresh()->credit_balance, 'no refund on reject');
    }
}
