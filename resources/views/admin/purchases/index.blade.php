@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl font-bold mb-4">{{ __('admin.purchases.title') }}</h1>

        <form method="GET" class="flex gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                <option value="all" @selected($status === 'all')>Tous</option>
                @foreach (['pending', 'paid', 'failed', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">{{ __('admin.purchases.col_uid') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.purchases.col_user') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.purchases.col_pack') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.purchases.col_amount') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.purchases.col_status') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.purchases.col_created') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($purchases as $p)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.purchases.show', $p->purchase_uid) }}'">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->purchase_uid }}</td>
                            <td class="px-4 py-3 text-xs">{{ $p->user?->email }}</td>
                            <td class="px-4 py-3 text-xs">{{ $p->unlockPack?->name }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($p->amount_dzd, 0, ',', ' ') }} DZD</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded-full bg-gray-100">{{ $p->status }}</span></td>
                            <td class="px-4 py-3 text-xs text-gray-400">{{ $p->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-6 text-center text-gray-500 text-sm">Aucun achat.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($purchases->hasPages()) <div class="p-3 border-t border-gray-100">{{ $purchases->links() }}</div> @endif
        </div>
    </div>
@endsection
