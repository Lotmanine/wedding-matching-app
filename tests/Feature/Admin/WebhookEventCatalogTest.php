<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\AdminWebhooksController;
use App\Jobs\DeliverWebhookJob;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Services\Webhooks\WebhookDispatcher;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * Régression : les trois événements « enrichis » du canal WhatsApp
 * (round.started, lead.accepted, lead.opened_to_marketplace) doivent être
 * abonnables depuis /admin/webhooks.
 *
 * Contexte du bug : App\Listeners\Whatsapp\* émettait ces événements, mais ils
 * étaient absents de AdminWebhooksController::AVAILABLE_EVENTS. La règle
 * `in:...` du formulaire rejetait donc leur création, et comme
 * WebhookDispatcher::dispatch() filtre via WebhookEndpoint::subscribesTo(),
 * aucun endpoint ne pouvait les recevoir : les notifications WhatsApp
 * (téléphones + boutons signés) étaient construites puis silencieusement jetées.
 *
 * Les tests d'événements existants créaient l'endpoint directement via le
 * modèle — ils contournaient la validation du formulaire et restaient donc
 * verts malgré une production non fonctionnelle. D'où ce test qui passe
 * explicitement par la route admin.
 */
class WebhookEventCatalogTest extends TestCase
{
    use RefreshDatabase;

    /** Les événements enrichis consommés par le workflow n8n `wedding-whatsapp`. */
    private const WHATSAPP_EVENTS = [
        'round.started',
        'lead.accepted',
        'lead.opened_to_marketplace',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    private function makeAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_whatsapp_events_are_listed_in_the_catalog(): void
    {
        foreach (self::WHATSAPP_EVENTS as $event) {
            $this->assertContains(
                $event,
                AdminWebhooksController::AVAILABLE_EVENTS,
                "L'événement `{$event}` doit être abonnable, sinon le canal WhatsApp est muet.",
            );
        }
    }

    public function test_admin_can_create_an_endpoint_subscribed_to_whatsapp_events(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.webhooks.store'), [
                'name'   => 'n8n WhatsApp',
                'url'    => 'https://n8n.example.com/webhook/wedding-whatsapp-outbound',
                'secret' => str_repeat('a', 32),
                'events' => self::WHATSAPP_EVENTS,
                'active' => true,
            ])
            ->assertSessionHasNoErrors();

        $endpoint = WebhookEndpoint::firstWhere('name', 'n8n WhatsApp');

        $this->assertNotNull($endpoint);
        foreach (self::WHATSAPP_EVENTS as $event) {
            $this->assertTrue($endpoint->subscribesTo($event));
        }
    }

    public function test_dispatcher_queues_a_delivery_for_each_whatsapp_event(): void
    {
        Bus::fake();

        WebhookEndpoint::create([
            'name'   => 'n8n WhatsApp',
            'url'    => 'https://n8n.example.com/webhook/wedding-whatsapp-outbound',
            'secret' => 'secret',
            'events' => self::WHATSAPP_EVENTS,
            'active' => true,
        ]);

        $dispatcher = app(WebhookDispatcher::class);

        foreach (self::WHATSAPP_EVENTS as $event) {
            $dispatcher->dispatch($event, ['service_request_id' => 'SRV-TEST-0001']);
        }

        Bus::assertDispatchedTimes(DeliverWebhookJob::class, count(self::WHATSAPP_EVENTS));
    }

    public function test_unknown_event_is_still_rejected(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.webhooks.store'), [
                'name'   => 'Bogus',
                'url'    => 'https://n8n.example.com/webhook/nope',
                'secret' => str_repeat('a', 32),
                'events' => ['definitely.not.an.event'],
                'active' => true,
            ])
            ->assertSessionHasErrors('events.0');
    }
}
