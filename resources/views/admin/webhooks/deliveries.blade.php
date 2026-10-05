@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.webhooks.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> Webhooks</a></nav>
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">{{ $endpoint->name }}</h1>
        <p class="text-xs text-[#9A958D] font-mono mb-6">{{ $endpoint->url }}</p>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <input type="text" name="event_type" value="{{ request('event_type') }}" placeholder="event_type" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors placeholder-[#9A958D]" />
            <select name="status" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                <option value="">Tous statuts</option>
                <option value="delivered" @selected(request('status') === 'delivered')>Livré</option>
                <option value="failed" @selected(request('status') === 'failed')>Échec</option>
                <option value="pending" @selected(request('status') === 'pending')>En attente</option>
            </select>
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">Filtrer</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">ID</th>
                            <th class="text-left px-5 py-4">Événement</th>
                            <th class="text-left px-5 py-4">Statut</th>
                            <th class="text-right px-5 py-4">Tent.</th>
                            <th class="text-left px-5 py-4">Créé</th>
                            <th class="text-left px-5 py-4">Livré</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($deliveries as $d)
                            <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs text-[#6B665E]">{{ $d->id }}</td>
                                <td class="px-5 py-3 text-xs font-mono text-[#1A1A1A]">{{ $d->event_type }}</td>
                                <td class="px-5 py-3 text-xs">
                                    @if ($d->delivered_at)
                                        <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">{{ $d->response_status }}</span>
                                    @elseif ($d->failed_at)
                                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-[#A84936] border border-red-100">{{ $d->response_status ?? 'err' }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-[#6B665E]">{{ $d->attempts }}</td>
                                <td class="px-5 py-3 text-xs text-[#9A958D]">{{ $d->created_at?->diffForHumans() }}</td>
                                <td class="px-5 py-3 text-xs text-[#9A958D]">{{ $d->delivered_at?->diffForHumans() ?? 'Non renseigné' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.webhooks.deliveries.replay', $d->id) }}" class="inline">@csrf
                                        <button class="text-[#C8956C] text-xs font-bold hover:underline">Rejouer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-6 text-center text-[#9A958D] text-sm">Aucune livraison.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($deliveries->hasPages()) <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $deliveries->links() }}</div> @endif
        </div>
    </div>
@endsection
