@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-4">{{ __('admin.requests.title') }}</h1>

        <form method="GET" class="flex flex-wrap gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Statut</label>
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                    <option value="all" @selected($status === 'all')>Tous</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilaya</label>
                <select name="wilaya" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                    <option value="">—</option>
                    @foreach ($wilayas as $w)
                        <option value="{{ $w->code }}" @selected($wilaya === $w->code)>{{ $w->code }} — {{ $w->name_fr }}</option>
                    @endforeach
                </select>
            </div>
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">{{ __('admin.requests.col_uid') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.requests.col_couple') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.requests.col_wilaya') }}</th>
                        <th class="text-left px-4 py-3">Services</th>
                        <th class="text-left px-4 py-3">{{ __('admin.requests.col_status') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.requests.col_created') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.requests.show', $r->wed_uid) }}'">
                            <td class="px-4 py-3 font-mono text-xs">{{ $r->wed_uid }}</td>
                            <td class="px-4 py-3">{{ $r->couple_first_name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $r->couple_wilaya_code }}{{ $r->couple_commune ? ' — ' . $r->couple_commune : '' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $r->serviceRequests->count() }} service(s)</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ $r->status?->value ?? $r->status }}</span>
                                @if ($r->deleted_at)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 ml-1">supprimée</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $r->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 text-sm">Aucune demande.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($requests->hasPages())
                <div class="p-3 border-t border-gray-100">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
