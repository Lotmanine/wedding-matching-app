@extends('layouts.provider')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-8 sm:p-10 text-center space-y-6">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-red-50 flex items-center justify-center">
                <x-z.icon name="error" :filled="true" class="text-[#A84936] text-3xl" />
            </div>

            <div>
                <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('credits.failure_title') }}</h1>
                <p class="mt-2 text-[#6B665E]">{{ __('credits.failure_sub') }}</p>
            </div>

            {{-- What we actually know about this payment: a sentence, never the stored technical code --}}
            @if ($explanation ?? null)
                <div class="p-3 rounded-lg bg-[#F7F4F0] border border-[#D4CFC7]/50 text-sm text-[#1A1A1A]">
                    <p>{{ $explanation }}</p>
                    @if (($teamNote ?? '') !== '')
                        <p class="mt-1 text-[#6B665E]">{{ __('credits.failure_team_note') }} <span>{{ $teamNote }}</span></p>
                    @endif
                </div>
            @endif

            <div class="text-sm text-[#6B665E] text-start space-y-2 bg-[#F0D9B5]/20 border border-[#C8956C]/20 rounded-xl p-4">
                {{-- Possible causes, not established facts: the payment service does not tell us why a card was declined --}}
                @if ($showCardCauses ?? true)
                    <p class="font-semibold text-[#1A1A1A]">{{ __('credits.failure_causes_title') }}</p>
                    <ul class="space-y-1.5">
                        @foreach (__('credits.failure_causes') as $cause)
                            <li class="flex items-start gap-2"><x-z.icon name="help" class="text-[#C8956C] text-base shrink-0 mt-0.5" />{{ $cause }}</li>
                        @endforeach
                    </ul>
                @endif
                <p>{{ ($offlineEnabled ?? false) ? __('credits.failure_next_offline') : __('credits.failure_next') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <x-z.btn :href="route('provider.credits.index')" variant="accent" size="lg" class="flex-1 justify-center">
                    {{ __('credits.failure_retry') }}
                </x-z.btn>
                <x-z.btn href="mailto:support@zawajpro.dz" variant="outline" size="lg" class="flex-1 justify-center">
                    {{ __('credits.failure_contact') }}
                </x-z.btn>
            </div>

            @if ($purchase)
                <p class="text-xs text-[#9A958D] font-mono">{{ $purchase->purchase_uid }}</p>
            @endif
        </div>
    </div>
@endsection
