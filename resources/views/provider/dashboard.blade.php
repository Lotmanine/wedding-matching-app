@extends('layouts.provider')

@section('content')
    @php $unlockCost = (int) config('wedding.credits.unlock_cost', 1); @endphp
    <div class="max-w-6xl mx-auto">
        {{-- Greeting --}}
        <div class="mb-8">
            <h1 class="font-serif text-3xl font-bold text-[#1A1A1A]">
                {{ __('provider.dashboard.greeting', ['name' => $user->name]) }}
            </h1>
        </div>

        @if ($profile && ! $profile->verified)
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-800 text-sm flex items-center gap-3">
                <x-z.icon name="schedule" class="text-xl shrink-0" />
                <span>{{ __('provider.dashboard.pending_banner') }}</span>
            </div>
        @endif

        @if ($profile && (int) $profile->credit_balance < $unlockCost)
            <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-800 text-sm flex items-center justify-between gap-3">
                <span class="flex items-center gap-3"><x-z.icon name="error" class="text-xl shrink-0" />{{ __('credits.low_balance_warning') }}</span>
                <x-z.btn :href="route('provider.credits.index')" variant="accent" size="sm" class="whitespace-nowrap shrink-0">{{ __('provider.nav.recharge') }}</x-z.btn>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            <a href="{{ route('provider.leads.index') }}" class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:border-[#C8956C]/40 transition-all">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <x-z.icon name="inbox" :filled="true" class="text-blue-600 text-xl" />
                </div>
                <div>
                    <p class="text-xl font-serif font-bold text-[#1A1A1A]">{{ $eligibleCount ?? 0 }}</p>
                    <p class="text-xs text-[#9A958D] font-medium">{{ __('provider.leads.title') }}</p>
                </div>
            </a>
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                    <x-z.icon name="emoji_events" :filled="true" class="text-green-600 text-xl" />
                </div>
                <div>
                    <p class="text-xl font-serif font-bold text-[#1A1A1A]">{{ $profile->leads_accepted ?? 0 }}</p>
                    <p class="text-xs text-[#9A958D] font-medium">{{ __('provider.dashboard.stat_accepted') }}</p>
                </div>
            </div>
            <a href="{{ route('provider.credits.index') }}" class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-4 flex items-center gap-3 hover:shadow-md hover:border-[#C8956C]/40 transition-all">
                <div class="w-10 h-10 rounded-xl bg-[#F0D9B5] flex items-center justify-center shrink-0">
                    <x-z.icon name="toll" :filled="true" class="text-[#C8956C] text-xl" />
                </div>
                <div>
                    <p class="text-xl font-serif font-bold text-[#1A1A1A]">{{ $profile->credit_balance ?? 0 }}</p>
                    <p class="text-xs text-[#9A958D] font-medium">{{ __('provider.dashboard.stat_credits') }}</p>
                </div>
            </a>
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#F7F4F0] flex items-center justify-center shrink-0">
                    <x-z.icon name="trending_up" :filled="true" class="text-[#C8956C] text-xl" />
                </div>
                <div>
                    <p class="text-xl font-serif font-bold text-[#1A1A1A]">{{ number_format((float) ($profile->acceptance_rate ?? 0), 0) }}%</p>
                    <p class="text-xs text-[#9A958D] font-medium">{{ __('provider.dashboard.stat_rate') }}</p>
                </div>
            </div>
        </div>

        @if (isset($recentLeads) && count($recentLeads) > 0)
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('Demandes récentes') }}</span>
                    <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
                    <a href="{{ route('provider.leads.index') }}" class="text-sm text-[#C8956C] font-semibold hover:underline whitespace-nowrap flex items-center gap-1">
                        {{ __('Voir toutes les demandes') }} <x-z.icon name="arrow_forward" class="text-sm" />
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($recentLeads as $lead)
                        <x-lead-card :lead="$lead" />
                    @endforeach
                </div>
            </section>
        @else
            <div class="p-10 rounded-2xl bg-white border border-[#D4CFC7]/50 shadow-sm text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-[#F0D9B5] flex items-center justify-center">
                    <x-z.icon name="inbox" :filled="true" class="text-[#C8956C] text-3xl" />
                </div>
                <h2 class="mt-4 font-serif text-xl font-bold text-[#1A1A1A]">{{ __('provider.dashboard.empty_title') }}</h2>
                <p class="mt-2 text-sm text-[#6B665E]">{{ __('provider.dashboard.empty_sub') }}</p>

                @if (! $profile || ! $profile->onboarding_completed)
                    <x-z.btn :href="route('provider.profile.edit')" variant="accent" size="md" class="mt-6">
                        {{ __('provider.dashboard.complete_profile_cta') }}
                    </x-z.btn>
                @endif
            </div>
        @endif
    </div>
@endsection
