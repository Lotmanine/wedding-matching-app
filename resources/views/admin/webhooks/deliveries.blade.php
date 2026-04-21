@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.webhooks.index') }}" class="hover:text-brand">← Webhooks</a></nav>
        <h1 class="font-display text-2xl font-bold mb-1">{{ $endpoint->name }}</h1>
        <p class="text-xs text-gray-500 font-mono mb-4">{{ $endpoint->url }}</p>

        <form method="GET" class="flex gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <input type="text" name="event_type" value="{{ request('event_type') }}" placeholder="event_type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono" />
            <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                <option value="">Tous statuts</option>
                <option value="delivered" @selected(request('status') === 'delivered')>Livré</option>
                <option value="failed" @selected(request('status') === 'failed')>Échec</option>
                <option value="pending" @selected(request('status') === 'pending')>En attente</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">ID</th>
                        <th class="text-left px-4 py-3">Événement</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Tent.</th>
                        <th class="text-left px-4 py-3">Créé</th>
                        <th class="text-left px-4 py-3">Livré</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($deliveries as $d)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $d->id }}</td>
                            <td class="px-4 py-3 text-xs font-mono">{{ $d->event_type }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if ($d->delivered_at)
                                    <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">{{ $d->response_status }}</span>
                                @elseif ($d->failed_at)
                                    <span class="px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ $d->response_status ?? 'err' }}</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-xs">{{ $d->attempts }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $d->created_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $d->delivered_at?->diffForHumans() ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.webhooks.deliveries.replay', $d->id) }}" class="inline">@csrf
                                    <button class="text-brand text-xs hover:underline">Rejouer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-gray-500 text-sm">Aucune livraison.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($deliveries->hasPages()) <div class="p-3 border-t border-gray-100">{{ $deliveries->links() }}</div> @endif
        </div>
    </div>
@endsection
