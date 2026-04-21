@extends('layouts.provider')

@section('content')
    @php
        $balance = (int) ($profile?->credit_balance ?? 0);
        $unlockCost = (int) config('zawaj.credits.unlock_cost', 1);
    @endphp

    <div class="max-w-5xl">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
            {{ __('credits.title') }}
        </h1>

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Balance card --}}
        <section class="bg-gradient-to-br from-emerald-50 to-white rounded-2xl border border-emerald-100 p-6 sm:p-8 mb-8">
            <p class="text-xs uppercase tracking-widest text-brand font-semibold">{{ __('credits.balance_label') }}</p>
            <p class="mt-2 font-display text-5xl font-bold text-brand">{{ $balance }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ __('credits.balance_unit', ['count' => $balance]) }}</p>
            <p class="mt-3 text-xs text-gray-500">ℹ️ {{ __('credits.balance_tooltip') }}</p>

            @if ($balance < $unlockCost)
                <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 text-sm">
                    {{ __('credits.low_balance_warning') }}
                </div>
            @endif
        </section>

        {{-- Packs --}}
        <section class="mb-8">
            <h2 class="font-display text-lg font-semibold text-gray-900 mb-4">{{ __('credits.packs_title') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($packs as $pack)
                    @php $perUnlock = $pack->unlocks_count > 0 ? (int) round($pack->price_dzd / $pack->unlocks_count) : 0; @endphp
                    <div @class([
                        'relative bg-white rounded-2xl p-6 border-2 shadow-sm hover:shadow-md transition',
                        'border-brand shadow-emerald-900/10' => $pack->is_popular,
                        'border-emerald-100/70' => ! $pack->is_popular,
                    ])>
                        @if ($pack->is_popular)
                            <span class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-brand text-white text-xs font-semibold uppercase tracking-wider">
                                ⭐ {{ __('credits.pack_popular') }}
                            </span>
                        @endif

                        <h3 class="font-display text-xl font-bold text-gray-900">{{ $pack->name }}</h3>
                        <p class="mt-4">
                            <span class="font-display text-4xl font-bold text-brand">{{ $pack->unlocks_count }}</span>
                            <span class="text-sm text-gray-500">crédits</span>
                        </p>
                        <p class="mt-2 font-semibold text-gray-800">{{ number_format($pack->price_dzd, 0, ',', ' ') }} DZD</p>
                        <p class="text-xs text-gray-500">{{ __('credits.pack_per_unlock', ['price' => number_format($perUnlock, 0, ',', ' ')]) }}</p>

                        <form method="POST" action="{{ route('provider.credits.checkout', $pack->slug) }}" class="mt-5">
                            @csrf
                            <button type="submit" @class([
                                'w-full py-2.5 rounded-xl font-semibold text-sm transition',
                                'bg-brand hover:bg-brand-dark text-white shadow-md shadow-emerald-900/10' => $pack->is_popular,
                                'border-2 border-emerald-200 hover:border-brand text-brand bg-white' => ! $pack->is_popular,
                            ])>
                                {{ __('credits.pack_cta') }}
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Recent transactions --}}
        <section class="bg-white rounded-2xl border border-emerald-100/70 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-lg font-semibold text-gray-900">{{ __('credits.recent_title') }}</h2>
                <a href="{{ route('provider.credits.history') }}" class="text-sm text-brand hover:underline">
                    {{ __('credits.full_history') }} →
                </a>
            </div>

            @if ($recent->isEmpty())
                <p class="text-sm text-gray-500 text-center py-6">{{ __('credits.no_transactions') }}</p>
            @else
                <ul class="divide-y divide-emerald-100/60">
                    @foreach ($recent as $entry)
                        <li class="py-3 flex items-center justify-between gap-4 text-sm">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ __('credits.type.' . $entry->type) }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $entry->notes ?? '—' }} · {{ $entry->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p @class([
                                    'font-semibold',
                                    'text-emerald-700' => $entry->delta > 0,
                                    'text-red-600' => $entry->delta < 0,
                                    'text-gray-700' => $entry->delta === 0,
                                ])>
                                    {{ $entry->delta > 0 ? '+' : '' }}{{ $entry->delta }}
                                </p>
                                <p class="text-xs text-gray-400">solde : {{ $entry->balance_after }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
