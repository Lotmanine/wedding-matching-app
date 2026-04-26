@php
    /** @var \App\Models\ServiceRequest $sr */
    $messages = \App\Models\WhatsappMessage::where('service_request_uid', $sr->service_uid)
        ->orderByDesc('id')
        ->limit(50)
        ->get();
    $counts = $messages->countBy('status');
@endphp

<section class="mt-8 bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-lg font-semibold text-gray-900">📱 WhatsApp Delivery</h2>
        <div class="flex gap-2 text-xs">
            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">✅ sent: {{ ($counts['sent'] ?? 0) + ($counts['delivered'] ?? 0) + ($counts['read'] ?? 0) }}</span>
            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">⏳ queued: {{ $counts['queued'] ?? 0 }}</span>
            <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700">❌ failed: {{ $counts['failed'] ?? 0 }}</span>
        </div>
    </div>

    @if ($messages->isEmpty())
        <p class="text-sm text-gray-500 italic">No WhatsApp messages recorded yet for this request.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="text-left text-gray-500 uppercase border-b border-gray-200">
                    <tr>
                        <th class="py-2">Sent</th>
                        <th class="py-2">Event</th>
                        <th class="py-2">Recipient</th>
                        <th class="py-2">Role</th>
                        <th class="py-2">Template</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">wamid</th>
                        <th class="py-2">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($messages as $m)
                        <tr>
                            <td class="py-1.5 text-gray-500 whitespace-nowrap">{{ $m->sent_at?->diffForHumans() ?? '—' }}</td>
                            <td class="py-1.5 font-mono">{{ $m->event_type }}</td>
                            <td class="py-1.5 font-mono">{{ $m->recipient_phone_e164 }}</td>
                            <td class="py-1.5">
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $m->role === 'provider' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $m->role }}</span>
                            </td>
                            <td class="py-1.5 font-mono">{{ $m->template_intent ?? '—' }}</td>
                            <td class="py-1.5">
                                @php
                                    $statusClass = match ($m->status) {
                                        'sent', 'delivered', 'read' => 'bg-emerald-100 text-emerald-800',
                                        'queued'                    => 'bg-amber-100 text-amber-800',
                                        'failed'                    => 'bg-red-100 text-red-700',
                                        default                     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="px-1.5 py-0.5 rounded-full {{ $statusClass }}">{{ $m->status }}</span>
                            </td>
                            <td class="py-1.5 font-mono text-[10px] text-gray-500 truncate max-w-[140px]">{{ $m->wa_message_id ?? '—' }}</td>
                            <td class="py-1.5 text-red-600 truncate max-w-[200px]">{{ $m->error ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
