@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-display text-2xl font-bold">Webhooks</h1>
            <a href="{{ route('admin.webhooks.create') }}" class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">+ Créer un webhook</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">Nom</th>
                        <th class="text-left px-4 py-3">URL</th>
                        <th class="text-left px-4 py-3">Événements</th>
                        <th class="text-center px-4 py-3">Livraisons</th>
                        <th class="text-center px-4 py-3">Actif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($endpoints as $e)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $e->name }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 truncate max-w-xs">{{ $e->url }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ((array) $e->events as $ev)
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-50 text-brand font-mono">{{ $ev }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-xs">
                                <a href="{{ route('admin.webhooks.deliveries', $e->id) }}" class="text-brand hover:underline">{{ $e->total_deliveries }} →</a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.webhooks.toggle', $e->id) }}" class="inline">@csrf
                                    <button class="text-xs px-2 py-0.5 rounded-full {{ $e->active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $e->active ? 'Actif' : 'Inactif' }}</button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.webhooks.edit', $e->id) }}" class="text-brand text-xs font-medium hover:underline">Éditer</a>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $e->id) }}" class="inline ml-2" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 text-sm">Aucun webhook configuré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
