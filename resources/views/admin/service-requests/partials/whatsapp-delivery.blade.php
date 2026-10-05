@php
    /** @var \App\Models\ServiceRequest $sr */
    $messages = \App\Models\WhatsappMessage::where('service_request_uid', $sr->service_uid)
        ->orderByDesc('id')
        ->limit(50)
        ->get();
    $counts = $messages->countBy('status');
@endphp

<section class="mt-8 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="flex items-center gap-2 font-serif text-lg font-bold text-[#1A1A1A]">
            <x-z.icon name="chat" :filled="true" class="text-xl text-[#25D366]" /> Messages WhatsApp
        </h2>
        <div class="flex flex-wrap gap-2 text-[11px] font-bold uppercase tracking-wider">
            <span class="px-2.5 py-0.5 rounded-full bg-[#25D366]/10 text-[#1ebe5d] border border-[#25D366]/30">Envoyés : {{ ($counts['sent'] ?? 0) + ($counts['delivered'] ?? 0) + ($counts['read'] ?? 0) }}</span>
            <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">En file : {{ $counts['queued'] ?? 0 }}</span>
            <span class="px-2.5 py-0.5 rounded-full bg-red-50 text-[#A84936] border border-red-100">En échec : {{ $counts['failed'] ?? 0 }}</span>
        </div>
    </div>

    @if ($messages->isEmpty())
        <p class="text-sm text-[#9A958D] italic">Aucun message WhatsApp enregistré pour cette demande.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#F7F4F0] text-left text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                        <th class="px-3 py-3">Envoi</th>
                        <th class="px-3 py-3">Événement</th>
                        <th class="px-3 py-3">Destinataire</th>
                        <th class="px-3 py-3">Rôle</th>
                        <th class="px-3 py-3">Modèle</th>
                        <th class="px-3 py-3">Statut</th>
                        <th class="px-3 py-3">ID WhatsApp</th>
                        <th class="px-3 py-3">Erreur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D4CFC7]/20">
                    @foreach ($messages as $m)
                        <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                            <td class="px-3 py-2 text-[#9A958D] whitespace-nowrap">{{ $m->sent_at?->diffForHumans() ?? 'Non renseigné' }}</td>
                            <td class="px-3 py-2 font-mono text-[#1A1A1A]">
                                @if ($m->direction === 'inbound')
                                    <span class="inline-flex text-[10px] px-1 py-0.5 rounded bg-[#F0D9B5]/50 text-[#7A5530] mr-1"><x-z.icon name="arrow_back" class="text-[10px]" /></span>
                                @endif
                                {{ $m->event_type }}
                            </td>
                            <td class="px-3 py-2 font-mono text-[#6B665E]">{{ $m->recipient_phone_e164 }}</td>
                            <td class="px-3 py-2">
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $m->role === 'provider' ? 'bg-[#25D366]/10 text-[#1ebe5d]' : 'bg-[#F0D9B5]/50 text-[#7A5530]' }}">{{ $m->role === 'provider' ? 'Prestataire' : 'Couple' }}</span>
                            </td>
                            <td class="px-3 py-2 font-mono text-[#6B665E]">{{ $m->template_intent ?? 'Non renseigné' }}</td>
                            <td class="px-3 py-2">
                                @php
                                    // Pill chain reflects Meta progression: sent → delivered → read.
                                    // `failed` short-circuits and shows red; inbound rows just show their own status.
                                    $isFailed = $m->status === 'failed';
                                    $rank = ['queued' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];
                                    $current = $rank[$m->status] ?? 0;
                                    $stages = [
                                        ['label' => 'Envoyé',  'idx' => 1],
                                        ['label' => 'Distribué', 'idx' => 2],
                                        ['label' => 'Lu',      'idx' => 3],
                                    ];
                                @endphp
                                @if ($isFailed)
                                    <span class="px-1.5 py-0.5 rounded-full bg-red-50 text-[#A84936]">Échec</span>
                                @elseif ($m->direction === 'inbound')
                                    <span class="px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-700">Reçu</span>
                                @else
                                    <span class="inline-flex gap-0.5 items-center">
                                        @foreach ($stages as $stage)
                                            <span @class([
                                                'px-1 py-0.5 text-[10px] rounded',
                                                'bg-[#25D366] text-white'          => $current >= $stage['idx'],
                                                'bg-[#F7F4F0] text-[#9A958D]'      => $current < $stage['idx'],
                                            ]) title="{{ $stage['label'] }}">
                                                {{ mb_substr($stage['label'], 0, 1) }}
                                            </span>
                                            @if (! $loop->last)
                                                <x-z.icon name="chevron_right" class="text-[#D4CFC7] text-[10px]" />
                                            @endif
                                        @endforeach
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-2 font-mono text-[10px] text-[#9A958D] truncate max-w-[140px]">{{ $m->wa_message_id ?? 'Non renseigné' }}</td>
                            <td class="px-3 py-2 text-[#A84936] truncate max-w-[200px]">{{ $m->error ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
