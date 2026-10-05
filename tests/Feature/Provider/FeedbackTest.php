<?php

declare(strict_types=1);

namespace Tests\Feature\Provider;

use App\Models\AuditLog;
use App\Models\Feedback;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Provider feedback (subject + message) → admin inbox + email alert.
 */
class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge(['role' => $role, 'status' => 'active'], $attributes));
        $user->assignRole($role);

        return $user;
    }

    private function sentMails(): \Illuminate\Support\Collection
    {
        return app('mailer')->getSymfonyTransport()->messages();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('provider.feedback.create'))->assertRedirect();
        $this->post(route('provider.feedback.store'), ['subject' => 'x', 'message' => 'y'])->assertRedirect();

        $this->assertSame(0, Feedback::count());
    }

    public function test_provider_can_view_the_form(): void
    {
        $this->actingAs($this->userWithRole('provider'))
            ->get(route('provider.feedback.create'))
            ->assertOk()
            ->assertSee('Remarques et suggestions');
    }

    public function test_provider_can_send_feedback_and_the_alert_address_is_emailed(): void
    {
        Setting::set('feedback.alert_email', 'alerts@zawajpro.dz');
        $this->userWithRole('admin', ['email' => 'boss@zawajpro.dz']);
        $provider = $this->userWithRole('provider');

        $this->actingAs($provider)->post(route('provider.feedback.store'), [
            'subject' => '  Bouton   cassé ',
            'message' => "Le bouton Accepter ne répond pas.\nSur mobile.",
        ])->assertRedirect(route('provider.feedback.create'))->assertSessionHas('status');

        $feedback = Feedback::firstOrFail();
        $this->assertSame($provider->id, $feedback->user_id);
        $this->assertSame('Bouton cassé', $feedback->subject);
        $this->assertSame('new', $feedback->fresh()->status);

        $mails = $this->sentMails();
        $this->assertCount(1, $mails);
        $email = $mails->first()->getOriginalMessage();
        $this->assertSame(['alerts@zawajpro.dz'], array_map(fn ($a) => $a->getAddress(), $email->getTo()));
        $this->assertStringContainsString('Bouton cassé', $email->getSubject());
        $this->assertStringContainsString('Le bouton Accepter ne répond pas.', $email->getTextBody());
    }

    public function test_feedback_is_stored_but_not_emailed_when_no_alert_address_is_set(): void
    {
        $this->userWithRole('admin', ['email' => 'boss@zawajpro.dz']);

        $this->actingAs($this->userWithRole('provider'))->post(route('provider.feedback.store'), [
            'subject' => 'Sans alerte',
            'message' => 'Aucune adresse configurée.',
        ])->assertRedirect(route('provider.feedback.create'))->assertSessionHas('status');

        $this->assertSame(1, Feedback::count());
        $this->assertCount(0, $this->sentMails());
    }

    public function test_subject_and_message_are_required(): void
    {
        $this->actingAs($this->userWithRole('provider'))
            ->post(route('provider.feedback.store'), ['subject' => '', 'message' => ''])
            ->assertSessionHasErrors(['subject', 'message']);

        $this->assertSame(0, Feedback::count());
    }

    public function test_admin_and_manager_can_read_the_inbox_but_provider_cannot(): void
    {
        $provider = $this->userWithRole('provider');
        Feedback::create(['user_id' => $provider->id, 'subject' => 'Sujet visible', 'message' => 'Corps du message']);

        $this->actingAs($this->userWithRole('admin'))->get(route('admin.feedback.index'))
            ->assertOk()->assertSee('Sujet visible')->assertSee('Corps du message');
        $this->actingAs($this->userWithRole('manager'))->get(route('admin.feedback.index'))
            ->assertOk()->assertSee('Sujet visible');
        $this->actingAs($provider)->get(route('admin.feedback.index'))->assertForbidden();
    }

    public function test_marking_as_handled_moves_it_out_of_the_new_tab(): void
    {
        $provider = $this->userWithRole('provider');
        $manager = $this->userWithRole('manager');
        $feedback = Feedback::create(['user_id' => $provider->id, 'subject' => 'A traiter', 'message' => 'Message']);

        $this->actingAs($manager)->post(route('admin.feedback.handle', $feedback->id))
            ->assertRedirect()->assertSessionHas('status');

        $feedback->refresh();
        $this->assertSame('handled', $feedback->status);
        $this->assertSame($manager->id, $feedback->handled_by_user_id);
        $this->assertNotNull($feedback->handled_at);
        $this->assertTrue(AuditLog::where('action', 'feedback.handle')->exists());

        $this->actingAs($manager)->get(route('admin.feedback.index'))->assertDontSee('A traiter');
        $this->actingAs($manager)->get(route('admin.feedback.index', ['status' => 'handled']))->assertSee('A traiter');

        // Second attempt is a no-op with an error flash.
        $this->actingAs($manager)->post(route('admin.feedback.handle', $feedback->id))->assertSessionHas('error');
    }
}
