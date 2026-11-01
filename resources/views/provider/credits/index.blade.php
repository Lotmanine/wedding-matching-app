@extends('layouts.provider')

@section('content')
    @php
        $balance = (int) ($profile?->credit_balance ?? 0);
        $unlockCost = (int) config('wedding.credits.unlock_cost', 1);
        $currency = $profile?->currency() ?? \App\Support\Money::currencyForCity(null);
    @endphp

    <div class="max-w-6xl mx-auto">
        <h1 class="font-serif text-3xl font-bold text-[#1A1A1A] mb-2">
            {{ __('credits.title') }}
        </h1>
        <p class="text-[#6B665E] text-sm mb-8">{{ trans_choice('credits.unlock_cost_line', $unlockCost, ['count' => $unlockCost]) }}</p>

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm flex items-center gap-3">
                <x-z.icon name="error" class="text-xl shrink-0" />{{ session('error') }}
            </div>
        @endif

        {{-- Balance card --}}
        <section class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 flex items-center justify-between mb-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">{{ __('credits.balance_label') }}</p>
                <p class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ $balance }}</p>
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

        {{-- Verification gate notice --}}
        @unless ($canBuy)
            <div class="mb-8 flex items-start gap-3 p-5 rounded-2xl bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-[#7A5530]">
                <x-z.icon name="hourglass_top" :filled="true" class="text-xl shrink-0" />
                <div>
                    <p class="font-semibold text-sm text-[#1A1A1A]">{{ __('Profile under review') }}</p>
                    <p class="text-sm mt-0.5">{{ __('credits.checkout.not_verified') }}</p>
                </div>
            </div>
        @endunless

        {{-- Packs --}}
        <section class="mb-10">
            <div class="flex items-center gap-3 mb-6">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('credits.packs_title') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:pt-3">
                @foreach ($packs as $pack)
                    @php $perUnlock = $pack->unlocks_count > 0 ? (int) round($pack->price / $pack->unlocks_count) : 0; @endphp
                    <div @class([
                        'relative rounded-2xl border bg-gradient-to-b p-6 flex flex-col shadow-sm',
                        'from-[#F0D9B5]/50 to-white border-[#C8956C] shadow-lg shadow-[#C8956C]/10' => $pack->is_popular,
                        'from-[#F7F4F0] to-white border-[#D4CFC7]' => ! $pack->is_popular,
                    ])>

                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ __($pack->name) }}</p>
                        <div class="font-serif text-5xl font-bold text-[#1A1A1A] mb-1">{{ $pack->unlocks_count }}</div>
                        <p class="text-sm text-[#6B665E] mb-1">{{ trans_choice('credits.unit', (int) $pack->unlocks_count) }}</p>
                        <p class="font-serif text-xl font-bold text-[#1A1A1A] mt-3 mb-1">{{ \App\Support\Money::format($pack->price, $currency) }}</p>
                        <p class="text-xs text-[#9A958D] mb-6">{{ __('credits.pack_per_unlock', ['price' => \App\Support\Money::format($perUnlock, $currency)]) }}</p>

                        @if ($canBuy)
                            <x-z.btn :href="route('provider.credits.pay', $pack->slug)" :variant="$pack->is_popular ? 'primary' : 'outline'" size="md" class="mt-auto w-full justify-center">
                                {{ __('credits.pack_cta') }}
                            </x-z.btn>
                        @else
                            <button type="button" disabled aria-disabled="true"
                                class="mt-auto w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-lg bg-[#F7F4F0] text-[#9A958D] cursor-not-allowed select-none">
                                <x-z.icon name="lock" class="text-base" /> {{ __('Verification required') }}
                            </button>
                        @endif
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
                            {{-- Platform-written entries are described in the reader's language; a note typed by a person is shown as stored. --}}
                            @php $entryDescription = $entry->systemDescription(); @endphp
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-[#1A1A1A] truncate">{{ $entry->typeLabel() }}</p>
                                <p class="text-xs text-[#9A958D] truncate">
                                    @if ($entryDescription !== null)
                                        {{ $entryDescription }} ·
                                    @elseif (filled($entry->notes))
                                        <span>{{ $entry->notes }}</span> ·
                                    @endif
                                    {{ $entry->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-end shrink-0">
                                <p @class([
                                    'font-bold font-serif text-lg',
                                    'text-green-600' => $entry->delta > 0,
                                    'text-[#A84936]' => $entry->delta < 0,
                                    'text-[#6B665E]' => $entry->delta === 0,
                                ])>
                                    {{ $entry->delta > 0 ? '+' : '' }}{{ $entry->delta }}
                                </p>
                                <p class="text-xs text-[#9A958D]">{{ __('balance: :balance', ['balance' => $entry->balance_after]) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
