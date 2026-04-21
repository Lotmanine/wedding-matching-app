@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-display text-2xl font-bold">{{ __('admin.packs.title') }}</h1>
            <a href="{{ route('admin.packs.create') }}" class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">+ {{ __('admin.packs.create_btn') }}</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">{{ __('admin.packs.col_name') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.packs.col_count') }}</th>
                        <th class="text-right px-4 py-3">{{ __('admin.packs.col_price') }}</th>
                        <th class="text-center px-4 py-3">{{ __('admin.packs.col_popular') }}</th>
                        <th class="text-center px-4 py-3">{{ __('admin.packs.col_order') }}</th>
                        <th class="text-center px-4 py-3">{{ __('admin.packs.col_active') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($packs as $p)
                        <tr>
                            <td class="px-4 py-3"><div class="font-medium">{{ $p->name }}</div><div class="text-xs text-gray-500 font-mono">{{ $p->slug }}</div></td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $p->unlocks_count }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($p->price_dzd, 0, ',', ' ') }} DZD</td>
                            <td class="px-4 py-3 text-center">{{ $p->is_popular ? '⭐' : '' }}</td>
                            <td class="px-4 py-3 text-center">{{ $p->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('admin.packs.toggle', $p->id) }}" class="inline">@csrf
                                    <button class="text-xs px-2 py-0.5 rounded-full {{ $p->active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $p->active ? 'Actif' : 'Inactif' }}</button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.packs.edit', $p->id) }}" class="text-brand text-xs font-medium hover:underline">Éditer</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
