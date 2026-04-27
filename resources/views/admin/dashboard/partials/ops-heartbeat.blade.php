@php
    $beat = \App\Models\N8nHeartbeat::latest('run_at')->first();
    $isOverdue = $beat && $beat->run_at->lt(now()->subMinutes(45));
    $sevClass = match ($beat?->severity) {
        'critical' => 'bg-red-100 text-red-700',
        'warn'     => 'bg-amber-100 text-amber-800',
        'none'     => 'bg-emerald-100 text-emerald-800',
        default    => 'bg-gray-100 text-gray-600',
    };
@endphp

<section class="mt-8 bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-display text-lg font-semibold text-gray-900">⚙️ Ops Heartbeat</h2>
        @if ($beat)
            <span class="text-xs px-2 py-0.5 rounded-full font-mono {{ $sevClass }}">
                {{ strtoupper($beat->severity) }}
            </span>
        @endif
    </div>

    @if (! $beat)
        <p class="text-sm text-gray-500 italic">No heartbeat received yet — n8n cron leg has not run since this table was created.</p>
    @else
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <dt class="text-gray-500 uppercase tracking-wider">Last beat</dt>
                <dd class="mt-0.5 font-mono">{{ $beat->run_at->diffForHumans() }}</dd>
            </div>
            @foreach (['stuck_matching', 'failed_webhooks_24h', 'failed_whatsapp_24h', 'n8n_errors_24h'] as $key)
                @php $count = (int) ($beat->snapshot_summary[$key] ?? 0); @endphp
                <div>
                    <dt class="text-gray-500 uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</dt>
                    <dd @class([
                        'mt-0.5 font-mono font-semibold',
                        'text-red-600'    => $count > 0 && in_array($key, ['failed_webhooks_24h', 'failed_whatsapp_24h', 'n8n_errors_24h'], true),
                        'text-amber-700'  => $count > 0 && $key === 'stuck_matching',
                        'text-gray-700'   => $count === 0,
                    ])>{{ $count }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($isOverdue)
            <p class="mt-3 text-xs px-3 py-2 rounded-lg bg-red-50 border border-red-200 text-red-700">
                ⚠️ Cron is overdue — last beat was more than 45 minutes ago. Check n8n workflow 4 status.
            </p>
        @endif

        <div class="mt-3 flex gap-3 text-[10px] text-gray-500">
            <span>📧 email alert {{ ($beat->alerts_sent['email'] ?? false) ? '✅ sent' : '— skipped' }}</span>
            <span>💬 telegram alert {{ ($beat->alerts_sent['telegram'] ?? false) ? '✅ sent' : '— skipped' }}</span>
        </div>
    @endif
</section>
