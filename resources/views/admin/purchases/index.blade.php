@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.purchases.title') }}</h1>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <select name="status" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                <option value="all" @selected($status === 'all')>{{ __('All') }}</option>
                @foreach (['pending', 'paid', 'failed', 'cancelled'] as $s)
                    <option value="{{ $s }}" @selected($status === $s)>{{ $s }}</option>
                @endforeach
            </select>
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">{{ __('Filter') }}</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('admin.purchases.col_uid') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.purchases.col_user') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.purchases.col_pack') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.purchases.col_amount') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.purchases.col_status') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.purchases.col_created') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($purchases as $p)
                            <tr class="hover:bg-[#F7F4F0]/50 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.purchases.show', $p->purchase_uid) }}'">
                                <td class="px-5 py-4"><code class="text-xs font-bold text-[#C8956C] bg-[#F0D9B5]/40 px-2 py-1 rounded">{{ $p->purchase_uid }}</code></td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $p->user?->email }}</td>
                                <td class="px-5 py-4 text-xs text-[#1A1A1A]">{{ $p->unlockPack?->name }}</td>
                                <td class="px-5 py-4 text-right font-bold text-[#1A1A1A]">{{ \App\Support\Money::format($p->amount, $p->currency) }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $pvariant = match ($p->status) {
                                            'paid'      => 'success',
                                            'failed'    => 'danger',
                                            'pending'   => 'warning',
                                            'cancelled' => 'muted',
                                            default     => 'default',
                                        };
                                    @endphp
                                    <x-z.badge :variant="$pvariant">{{ $p->status }}</x-z.badge>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#9A958D]">{{ $p->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No purchases.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($purchases->hasPages()) <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $purchases->links() }}</div> @endif
        </div>
    </div>
@endsection
