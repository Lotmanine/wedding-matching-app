<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminWebhooksController extends Controller
{
    public const AVAILABLE_EVENTS = [
        // "Thin" domain events (minimal payload: identifiers only).
        'wed_request.submitted',
        'service_request.round_started',
        'service_request.round_timed_out',
        'service_request.accepted',
        'service_request.refused',
        'service_request.opened_to_marketplace',
        'provider.registered',
        'purchase.completed',

        // "Rich" events meant for the WhatsApp channel (App\Listeners\Whatsapp\*).
        // They carry the recipients: E.164 phone numbers, preferred language and
        // signed button payloads (Crypt::encryptString) that n8n cannot build
        // itself. Without these three entries, WebhookDispatcher::dispatch()
        // filtered these events out and NO WhatsApp notification was sent.
        'round.started',
        'lead.accepted',
        'lead.opened_to_marketplace',
    ];

    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(): View
    {
        $endpoints = WebhookEndpoint::withCount(['deliveries as total_deliveries'])->get();

        return view('admin.webhooks.index', compact('endpoints'));
    }

    public function create(): View
    {
        return view('admin.webhooks.form', [
            'endpoint' => new WebhookEndpoint(['active' => true, 'events' => [], 'secret' => bin2hex(random_bytes(16))]),
            'availableEvents' => self::AVAILABLE_EVENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateForm($request);
        $data['secret'] = $data['secret'] ?: bin2hex(random_bytes(16));

        $endpoint = WebhookEndpoint::create($data);
        $this->audit->log('webhook_endpoint.create', $endpoint, ['events' => $data['events']]);

        return redirect()->route('admin.webhooks.index')->with('status', __('Webhook created.'));
    }

    public function edit(int $id): View
    {
        return view('admin.webhooks.form', [
            'endpoint' => WebhookEndpoint::findOrFail($id),
            'availableEvents' => self::AVAILABLE_EVENTS,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $data = $this->validateForm($request);
        if (empty($data['secret'])) {
            unset($data['secret']);
        }

        $endpoint->update($data);
        $this->audit->log('webhook_endpoint.update', $endpoint, ['events' => $data['events'] ?? []]);

        return redirect()->route('admin.webhooks.index')->with('status', __('Webhook updated.'));
    }

    public function toggle(int $id): RedirectResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $endpoint->forceFill(['active' => ! $endpoint->active])->save();

        $this->audit->log('webhook_endpoint.toggle', $endpoint, ['active' => $endpoint->active]);

        return back()->with('status', $endpoint->active ? __('Enabled.') : __('Disabled.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $this->audit->log('webhook_endpoint.delete', $endpoint);
        $endpoint->delete();

        return redirect()->route('admin.webhooks.index')->with('status', __('Webhook deleted.'));
    }

    public function deliveries(Request $request, int $id): View
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $query = $endpoint->deliveries()->latest('created_at');

        if ($ev = $request->query('event_type')) {
            $query->where('event_type', $ev);
        }
        if ($st = $request->query('status')) {
            match ($st) {
                'delivered' => $query->whereNotNull('delivered_at'),
                'failed'    => $query->whereNotNull('failed_at')->whereNull('delivered_at'),
                'pending'   => $query->whereNull('delivered_at')->whereNull('failed_at'),
                default     => null,
            };
        }

        $deliveries = $query->paginate(50)->withQueryString();

        return view('admin.webhooks.deliveries', compact('endpoint', 'deliveries'));
    }

    public function replay(int $id): RedirectResponse
    {
        $delivery = WebhookDelivery::findOrFail($id);

        DeliverWebhookJob::dispatch(
            $delivery->webhook_endpoint_id,
            $delivery->event_type,
            (array) $delivery->payload,
        );

        $this->audit->log('webhook_delivery.replay', $delivery);

        return back()->with('status', __('Delivery requeued.'));
    }

    private function validateForm(Request $request): array
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'url'        => ['required', 'url', 'max:500'],
            'secret'     => ['nullable', 'string', 'max:100'],
            'events'     => ['required', 'array', 'min:1'],
            'events.*'   => ['string', 'in:' . implode(',', self::AVAILABLE_EVENTS)],
            'active'     => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', true);

        return $data;
    }
}
