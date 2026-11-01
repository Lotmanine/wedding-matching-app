@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.requests.title') }}</h1>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('Status') }}</label>
                <select name="status" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                    <option value="all" @selected($status === 'all')>{{ __('All') }}</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('City') }}</label>
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
                            <th class="text-left px-5 py-4">{{ __('admin.requests.col_uid') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.requests.col_couple') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.requests.col_city') }}</th>
                            <th class="text-left px-5 py-4">{{ __('Services') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.requests.col_status') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.requests.col_created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($requests as $r)
                            <tr class="hover:bg-[#F7F4F0]/50 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.requests.show', $r->wed_uid) }}'">
                                <td class="px-5 py-4"><code class="text-xs font-bold text-[#C8956C] bg-[#F0D9B5]/40 px-2 py-1 rounded">{{ $r->wed_uid }}</code></td>
                                <td class="px-5 py-4 font-semibold text-[#1A1A1A]">{{ $r->couple_first_name }}</td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $r->couple_city_code }}{{ $r->couple_district ? ' · ' . $r->couple_district : '' }}</td>
                                <td class="px-5 py-4 text-xs text-[#9A958D]">{{ trans_choice(':count service|:count services', $r->serviceRequests->count()) }}</td>
                                <td class="px-5 py-4">
                                    <x-z.badge variant="default">{{ $r->status?->value ?? $r->status }}</x-z.badge>
                                    @if ($r->deleted_at)
                                        <x-z.badge variant="danger" class="ml-1">{{ __('deleted') }}</x-z.badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-[#9A958D]">{{ $r->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No requests.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($requests->hasPages())
                <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
@endsection
