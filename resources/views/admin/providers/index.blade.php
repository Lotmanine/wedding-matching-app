@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.providers.title') }}</h1>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('Search') }}</label>
                <input name="q" value="{{ $search }}" placeholder="{{ __('admin.providers.search_placeholder') }}" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors placeholder-[#9A958D]" />
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.providers.filter_status') }}</label>
                <select name="status" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                    <option value="all"       @selected($status === 'all')>{{ __('admin.providers.status_all') }}</option>
                    <option value="verified"  @selected($status === 'verified')>{{ __('admin.providers.status_verified') }}</option>
                    <option value="pending"   @selected($status === 'pending')>{{ __('admin.providers.status_pending') }}</option>
                    <option value="suspended" @selected($status === 'suspended')>{{ __('admin.providers.status_suspended') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.providers.filter_city') }}</label>
                <select name="city" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                    <option value="">{{ __('All cities') }}</option>
                    @foreach ($cities as $w)
                        <option value="{{ $w->code }}" @selected($city === $w->code)>{{ $w->code }} · {{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">{{ __('Filter') }}</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('admin.providers.col_business') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.providers.col_email') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.providers.col_city') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.providers.col_status') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.providers.col_credits') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.providers.col_stats') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.providers.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($providers as $p)
                            @php $prof = $p->providerProfile; @endphp
                            <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#F0D9B5] flex items-center justify-center text-xs font-bold text-[#1A1A1A] shrink-0 uppercase">{{ mb_substr($prof?->business_name ?? $p->name, 0, 1) }}</div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-[#1A1A1A]">{{ $prof?->business_name ?? __('Not specified') }}</div>
                                            <div class="text-xs text-[#9A958D]">{{ $p->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="text-[#1A1A1A]">{{ $p->email }}</div>
                                    <div class="text-[#9A958D] font-mono">{{ $p->phone }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $prof?->primary_city_code ?? __('Not specified') }}</td>
                                <td class="px-5 py-4">
                                    @if ($prof?->suspended_at)
                                        <x-z.badge variant="danger">{{ __('Suspended') }}</x-z.badge>
                                    @elseif ($prof?->verified)
                                        <x-z.badge variant="success">{{ __('Verified') }}</x-z.badge>
                                    @else
                                        <x-z.badge variant="warning">{{ __('Pending') }}</x-z.badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-bold text-[#1A1A1A]">{{ $prof?->credit_balance ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-xs text-[#6B665E]">
                                    {{ $prof?->leads_accepted ?? 0 }}/{{ $prof?->leads_received ?? 0 }}
                                    @if ($prof && $prof->rating_count > 0)
                                        <div class="text-[10px] text-[#C8956C]">⭐ {{ number_format($prof->rating_avg, 1) }} ({{ $prof->rating_count }})</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.providers.show', $p->id) }}" class="text-[#C8956C] text-xs font-bold hover:underline">{{ __('admin.providers.action_view') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No providers.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($providers->hasPages())
                <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $providers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
