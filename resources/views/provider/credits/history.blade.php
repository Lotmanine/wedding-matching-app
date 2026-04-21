@extends('layouts.provider')

@section('content')
    <div class="max-w-5xl">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-6">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">
                {{ __('credits.history_title') }}
            </h1>
            <a href="{{ route('provider.credits.history', ['format' => 'csv', 'type' => $type]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-emerald-200 hover:border-brand text-brand text-sm font-semibold transition">
                📄 {{ __('credits.history_export') }}
            </a>
        </div>

        <form method="GET" action="{{ route('provider.credits.history') }}" class="flex flex-wrap gap-3 mb-6 items-end">
            <div>
                <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">{{ __('credits.history_filter') }}</label>
                <select name="type" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                    <option value="all" @selected($type === 'all')>{{ __('credits.history_all') }}</option>
                    @foreach (['purchase','unlock','refund','bonus','admin_adjustment'] as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ __('credits.type.' . $t) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
            @if ($type !== 'all')
                <a href="{{ route('provider.credits.history') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-brand">✕</a>
            @endif
        </form>

        <div class="bg-white rounded-2xl border border-emerald-100/70 shadow-sm overflow-hidden">
            @if ($entries->isEmpty())
                <p class="text-sm text-gray-500 text-center py-10">{{ __('credits.no_transactions') }}</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-emerald-50/50 text-xs uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="text-left px-5 py-3">Date</th>
                            <th class="text-left px-5 py-3">Type</th>
                            <th class="text-left px-5 py-3">Description</th>
                            <th class="text-right px-5 py-3">Delta</th>
                            <th class="text-right px-5 py-3">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100/60">
                        @foreach ($entries as $e)
                            <tr class="hover:bg-emerald-50/30">
                                <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $e->created_at?->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                <td class="px-5 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ __('credits.type.' . $e->type) }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-700 max-w-xs truncate">{{ $e->notes ?? '—' }}</td>
                                <td @class([
                                    'px-5 py-3 text-right font-semibold whitespace-nowrap',
                                    'text-emerald-700' => $e->delta > 0,
                                    'text-red-600' => $e->delta < 0,
                                ])>
                                    {{ $e->delta > 0 ? '+' : '' }}{{ $e->delta }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600 whitespace-nowrap">{{ $e->balance_after }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($entries->hasPages())
                    <div class="px-5 py-3 border-t border-emerald-100/60">{{ $entries->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection
