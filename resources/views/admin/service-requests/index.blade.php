@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.service_requests.title') }}</h1>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <select name="status" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                <option value="all" @selected($status === 'all')>Tous statuts</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">Filtrer</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">Service UID</th>
                            <th class="text-left px-5 py-4">Wed UID</th>
                            <th class="text-left px-5 py-4">Service</th>
                            <th class="text-left px-5 py-4">Statut</th>
                            <th class="text-left px-5 py-4">Tour</th>
                            <th class="text-left px-5 py-4">Accepté par</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($items as $sr)
                            <tr class="hover:bg-[#F7F4F0]/50 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.service_requests.show', $sr->service_uid) }}'">
                                <td class="px-5 py-4"><code class="text-xs font-bold text-[#C8956C] bg-[#F0D9B5]/40 px-2 py-1 rounded">{{ $sr->service_uid }}</code></td>
                                <td class="px-5 py-4 font-mono text-xs text-[#6B665E]">{{ $sr->wedRequest?->wed_uid }}</td>
                                <td class="px-5 py-4 text-[#1A1A1A]">{{ $sr->service_type?->label() }}</td>
                                <td class="px-5 py-4"><x-z.badge variant="default">{{ $sr->status?->value ?? $sr->status }}</x-z.badge></td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $sr->current_round }}</td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $sr->unlockedBy?->email ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-[#9A958D] text-sm">Aucune demande.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages()) <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $items->links() }}</div> @endif
        </div>
    </div>
@endsection
