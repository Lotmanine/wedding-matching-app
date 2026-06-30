@extends('layouts.provider')

@section('content')
    @php
        $balance = (int) ($profile?->credit_balance ?? 0);
        $unlockCost = (int) config('zawaj.credits.unlock_cost', 1);
    @endphp

    <div class="max-w-6xl mx-auto">
        <h1 class="font-serif text-3xl font-bold text-[#1A1A1A] mb-2">
            {{ __('credits.title') }}
        </h1>
        <p class="text-[#6B665E] text-sm mb-8">{{ __('credits.balance_tooltip') }}</p>

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm flex items-center gap-3">
                <x-z.icon name="error" class="text-xl shrink-0" />{{ session('error') }}
            </div>
        @endif

        {{-- Balance card --}}
        <section class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 flex items-center justify-between mb-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">{{ __('credits.balance_label') }}</p>
                <p class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ $balance }} <span class="text-xl text-[#9A958D] font-sans">{{ __('credits.balance_unit', ['count' => $balance]) }}</span></p>
                @if ($balance < $unlockCost)
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 text-sm">
                        <x-z.icon name="warning" class="text-base" />{{ __('credits.low_balance_warning') }}
                    </div>
                @endif
            </div>
            <div class="w-16 h-16 rounded-2xl bg-[#F0D9B5] flex items-center justify-center shrink-0">
                <x-z.icon name="toll" :filled="true" class="text-[#C8956C] text-3xl" />
            </div>
        </section>

        {{-- Packs --}}
        <section class="mb-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('credits.packs_title') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:pt-3">
                @foreach ($packs as $pack)
                    @php $perUnlock = $pack->unlocks_count > 0 ? (int) round($pack->price_dzd / $pack->unlocks_count) : 0; @endphp
                    <div @class([
                        'relative rounded-2xl border bg-gradient-to-b p-6 flex flex-col shadow-sm',
                        'from-[#F0D9B5]/50 to-white border-[#C8956C] shadow-lg shadow-[#C8956C]/10' => $pack->is_popular,
                        'from-[#F7F4F0] to-white border-[#D4CFC7]' => ! $pack->is_popular,
                    ])>
                        @if ($pack->is_popular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <x-z.badge variant="accent">{{ __('credits.pack_popular') }}</x-z.badge>
                            </div>
                        @endif

                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ $pack->name }}</p>
                        <div class="font-serif text-5xl font-bold text-[#1A1A1A] mb-1">{{ $pack->unlocks_count }}</div>
                        <p class="text-sm text-[#6B665E] mb-1">crédits</p>
                        <p class="font-serif text-xl font-bold text-[#1A1A1A] mt-3 mb-1">{{ number_format($pack->price_dzd, 0, ',', ' ') }} DZD</p>
                        <p class="text-xs text-[#9A958D] mb-6">{{ __('credits.pack_per_unlock', ['price' => number_format($perUnlock, 0, ',', ' ')]) }}</p>

                        <form method="POST" action="{{ route('provider.credits.checkout', $pack->slug) }}" class="mt-auto">
                            @csrf
                            <x-z.btn type="submit" :variant="$pack->is_popular ? 'primary' : 'outline'" size="md" class="w-full justify-center">
                                {{ __('credits.pack_cta') }}
                            </x-z.btn>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Recent transactions --}}
        <section class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#D4CFC7]/30 flex items-center justify-between">
                <h2 class="font-semibold text-[#1A1A1A]">{{ __('credits.recent_title') }}</h2>
                <a href="{{ route('provider.credits.history') }}" class="text-sm text-[#C8956C] font-semibold hover:underline flex items-center gap-1">
                    {{ __('credits.full_history') }} <x-z.icon name="arrow_forward" class="text-sm" />
                </a>
            </div>

            @if ($recent->isEmpty())
                <p class="text-sm text-[#9A958D] text-center py-10">{{ __('credits.no_transactions') }}</p>
            @else
                <div class="divide-y divide-[#D4CFC7]/20">
                    @foreach ($recent as $entry)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1A1A1A] truncate">{{ __('credits.type.' . $entry->type) }}</p>
                                <p class="text-xs text-[#9A958D] truncate">{{ $entry->notes ?? '—' }} · {{ $entry->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p @class([
                                    'font-bold font-serif text-lg',
                                    'text-green-600' => $entry->delta > 0,
                                    'text-[#A84936]' => $entry->delta < 0,
                                    'text-[#6B665E]' => $entry->delta === 0,
                                ])>
                                    {{ $entry->delta > 0 ? '+' : '' }}{{ $entry->delta }}
                                </p>
                                <p class="text-xs text-[#9A958D]">solde : {{ $entry->balance_after }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
