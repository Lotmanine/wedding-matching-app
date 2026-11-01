@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.packs.title') }}</h1>
            </div>
            <x-z.btn :href="route('admin.packs.create')" variant="accent" size="sm" icon="add">{{ __('admin.packs.create_btn') }}</x-z.btn>
        </div>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('admin.packs.col_name') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.packs.col_count') }}</th>
                            <th class="text-right px-5 py-4">{{ __('admin.packs.col_price') }}</th>
                            <th class="text-center px-5 py-4">{{ __('admin.packs.col_popular') }}</th>
                            <th class="text-center px-5 py-4">{{ __('admin.packs.col_order') }}</th>
                            <th class="text-center px-5 py-4">{{ __('admin.packs.col_active') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @foreach ($packs as $p)
                            <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                <td class="px-5 py-4"><div class="font-semibold text-[#1A1A1A]">{{ $p->name }}</div><div class="text-xs text-[#9A958D] font-mono">{{ $p->slug }}</div></td>
                                <td class="px-5 py-4 text-right font-bold text-[#1A1A1A]">{{ $p->unlocks_count }}</td>
                                <td class="px-5 py-4 text-right text-[#1A1A1A]">{{ \App\Support\Money::format($p->price, \App\Support\Money::currencyForCity(null)) }}</td>
                                <td class="px-5 py-4 text-center">{{ $p->is_popular ? '⭐' : '' }}</td>
                                <td class="px-5 py-4 text-center text-[#6B665E]">{{ $p->sort_order }}</td>
                                <td class="px-5 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.packs.toggle', $p->id) }}" class="inline">@csrf
                                        <button class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase border {{ $p->active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-[#F7F4F0] text-[#9A958D] border-[#D4CFC7]' }}">{{ $p->active ? 'Actif' : 'Inactif' }}</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.packs.edit', $p->id) }}" class="text-[#C8956C] text-xs font-bold hover:underline">Éditer</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
