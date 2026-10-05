@php
    $beat = \App\Models\N8nHeartbeat::latest('run_at')->first();
    $isOverdue = $beat && $beat->run_at->lt(now()->subMinutes(45));
    $sevClass = match ($beat?->severity) {
        'critical' => 'bg-red-50 text-[#A84936] border-red-100',
        'warn'     => 'bg-amber-50 text-amber-700 border-amber-200',
        'none'     => 'bg-[#F0D9B5] text-[#7A5530] border-[#C8956C]/30',
        default    => 'bg-[#F7F4F0] text-[#6B665E] border-[#D4CFC7]',
    };
    $sevLabel = ['critical' => 'Critique', 'warn' => 'Alerte', 'none' => 'Tout va bien'][$beat?->severity] ?? '';
    $metricLabels = [
        'stuck_matching'      => 'Demandes bloquées',
        'failed_webhooks_24h' => 'Webhooks en échec (24 h)',
        'failed_whatsapp_24h' => 'WhatsApp en échec (24 h)',
        'n8n_errors_24h'      => 'Erreurs n8n (24 h)',
    ];
@endphp

<section class="mt-8 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
        <h2 class="flex items-center gap-2 font-serif text-lg font-bold text-[#1A1A1A]">
            <x-z.icon name="settings_heart" :filled="true" class="text-xl text-[#C8956C]" /> Supervision n8n
        </h2>
        @if ($beat)
            <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $sevClass }}">
                {{ $sevLabel }}
            </span>
        @endif
    </div>

    @if (! $beat)
        <p class="text-sm text-[#9A958D] italic">Aucun contrôle reçu pour le moment : la vérification planifiée de n8n n'a pas encore tourné.</p>
    @else
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-lg bg-[#F7F4F0]">
                <dt class="text-[10px] font-bold text-[#9A958D] uppercase tracking-wider">Dernier contrôle</dt>
                <dd class="mt-1 font-mono font-semibold text-[#1A1A1A]">{{ $beat->run_at->diffForHumans() }}</dd>
            </div>
            @foreach (['stuck_matching', 'failed_webhooks_24h', 'failed_whatsapp_24h', 'n8n_errors_24h'] as $key)
                @php $count = (int) ($beat->snapshot_summary[$key] ?? 0); @endphp
                <div class="p-3 rounded-lg bg-[#F7F4F0]">
                    <dt class="text-[10px] font-bold text-[#9A958D] uppercase tracking-wider">{{ $metricLabels[$key] }}</dt>
                    <dd @class([
                        'mt-1 font-mono font-semibold',
                        'text-[#A84936]'  => $count > 0 && in_array($key, ['failed_webhooks_24h', 'failed_whatsapp_24h', 'n8n_errors_24h'], true),
                        'text-[#7A5530]'  => $count > 0 && $key === 'stuck_matching',
                        'text-[#1A1A1A]'  => $count === 0,
                    ])>{{ $count }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($isOverdue)
            <p class="mt-3 flex items-center gap-2 text-xs px-3 py-2 rounded-lg bg-red-50 border border-red-100 text-[#A84936]">
                <x-z.icon name="warning" :filled="true" class="text-base" />
                Contrôle en retard : le dernier date de plus de 45 minutes. Vérifiez que le workflow zawaj-core est actif dans n8n.
            </p>
        @endif

        <div class="mt-3 flex gap-4 text-[10px] text-[#9A958D]">
            <span class="flex items-center gap-1"><x-z.icon name="mail" class="text-sm" /> Alerte e-mail : {{ ($beat->alerts_sent['email'] ?? false) ? '✅ envoyée' : 'non envoyée' }}</span>
            <span class="flex items-center gap-1"><x-z.icon name="chat" class="text-sm" /> Alerte Telegram : {{ ($beat->alerts_sent['telegram'] ?? false) ? '✅ envoyée' : 'non envoyée' }}</span>
        </div>
    @endif
</section>
