@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-4">{{ __('admin.service_requests.title') }}</h1>

        <form method="GET" class="flex gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                <option value="all" @selected($status === 'all')>Tous statuts</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">Service UID</th>
                        <th class="text-left px-4 py-3">Wed UID</th>
                        <th class="text-left px-4 py-3">Service</th>
                        <th class="text-left px-4 py-3">Statut</th>
                        <th class="text-left px-4 py-3">Tour</th>
                        <th class="text-left px-4 py-3">Accepté par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $sr)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.service_requests.show', $sr->service_uid) }}'">
                            <td class="px-4 py-3 font-mono text-xs">{{ $sr->service_uid }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $sr->wedRequest?->wed_uid }}</td>
                            <td class="px-4 py-3">{{ $sr->service_type?->label() }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ $sr->status?->value ?? $sr->status }}</span></td>
                            <td class="px-4 py-3 text-xs">{{ $sr->current_round }}</td>
                            <td class="px-4 py-3 text-xs">{{ $sr->unlockedBy?->email ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 text-sm">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($items->hasPages()) <div class="p-3 border-t border-gray-100">{{ $items->links() }}</div> @endif
        </div>
    </div>
@endsection
