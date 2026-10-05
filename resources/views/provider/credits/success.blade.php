@extends('layouts.provider')

@section('content')
    <div class="max-w-2xl mx-auto"
         @if ($purchase->status === 'pending') x-data x-init="setTimeout(() => window.location.reload(), 3000)" @endif
    >
        @if ($purchase->status === 'paid')
            <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-lg p-8 sm:p-10 text-center space-y-6">
                <div class="mx-auto w-20 h-20 rounded-2xl bg-green-50 flex items-center justify-center">
                    <x-z.icon name="check_circle" :filled="true" class="text-green-600 text-5xl" />
                </div>

                <div>
                    <h1 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ __('credits.success_title') }}</h1>
                    <p class="mt-2 text-[#6B665E]">{{ __('credits.success_sub') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 p-5 rounded-xl bg-[#F0D9B5]/30 border border-[#C8956C]/20 text-start">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __((string) $purchase->unlockPack?->name) }}</p>
                        <p class="font-serif text-3xl font-bold text-[#1A1A1A]">+{{ $purchase->unlocks_count }}</p>
                        <p class="text-xs text-[#9A958D]">{{ trans_choice('crédits', (int) $purchase->unlocks_count) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Solde total') }}</p>
                        <p class="font-serif text-3xl font-bold text-[#1A1A1A]">{{ $profile?->credit_balance ?? 0 }}</p>
                        <p class="text-xs text-[#9A958D]">{{ trans_choice('crédits', (int) ($profile?->credit_balance ?? 0)) }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <x-z.btn :href="route('provider.leads.index')" variant="accent" size="lg" icon="arrow_forward" class="w-full justify-center">
                        {{ __('credits.success_cta') }}
                    </x-z.btn>
                    <x-z.btn :href="route('provider.credits.purchase.show', $purchase->purchase_uid) . '?format=pdf'" variant="outline" size="lg" icon="picture_as_pdf" class="w-full justify-center">
                        {{ __('credits.success_pdf') }}
                    </x-z.btn>
                </div>

                <p class="text-xs text-[#9A958D] font-mono">{{ $purchase->purchase_uid }}</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-8 sm:p-10 text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center">
                    <x-z.icon name="schedule" :filled="true" class="text-amber-600 text-3xl" />
                </div>
                <h1 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ __('credits.success_pending_title') }}</h1>
                <p class="text-sm text-[#6B665E]">{{ __('credits.success_pending_sub') }}</p>
                <p class="text-xs text-[#9A958D] font-mono">{{ $purchase->purchase_uid }}</p>
            </div>
        @endif
    </div>
@endsection
