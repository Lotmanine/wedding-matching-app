@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-4">{{ __('admin.providers.title') }}</h1>

        <form method="GET" class="flex flex-wrap gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Recherche</label>
                <input name="q" value="{{ $search }}" placeholder="{{ __('admin.providers.search_placeholder') }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-brand focus:outline-none" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('admin.providers.filter_status') }}</label>
                <select name="status" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                    <option value="all"       @selected($status === 'all')>{{ __('admin.providers.status_all') }}</option>
                    <option value="verified"  @selected($status === 'verified')>{{ __('admin.providers.status_verified') }}</option>
                    <option value="pending"   @selected($status === 'pending')>{{ __('admin.providers.status_pending') }}</option>
                    <option value="suspended" @selected($status === 'suspended')>{{ __('admin.providers.status_suspended') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('admin.providers.filter_wilaya') }}</label>
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
                        <th class="text-left px-4 py-3">{{ __('admin.providers.col_business') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.providers.col_email') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.providers.col_wilaya') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.providers.col_status') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.providers.col_credits') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.providers.col_stats') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.providers.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($providers as $p)
                        @php $prof = $p->providerProfile; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $prof?->business_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $p->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div>{{ $p->email }}</div>
                                <div class="text-gray-500 font-mono">{{ $p->phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $prof?->primary_wilaya_code ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($prof?->suspended_at)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Suspendu</span>
                                @elseif ($prof?->verified)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Vérifié</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">En attente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $prof?->credit_balance ?? 0 }}</td>
                            <td class="px-4 py-3 text-right text-xs text-gray-600">
                                {{ $prof?->leads_accepted ?? 0 }}/{{ $prof?->leads_received ?? 0 }}
                                @if ($prof && $prof->rating_count > 0)
                                    <div class="text-[10px]">⭐ {{ number_format($prof->rating_avg, 1) }} ({{ $prof->rating_count }})</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.providers.show', $p->id) }}" class="text-brand text-xs font-medium hover:underline">{{ __('admin.providers.action_view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-6 text-center text-gray-500 text-sm">Aucun prestataire.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($providers->hasPages())
                <div class="p-3 border-t border-gray-100">{{ $providers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
