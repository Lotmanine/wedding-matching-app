@extends('layouts.provider')

@section('content')
    <div class="max-w-5xl">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">
            {{ __('provider.dashboard.greeting', ['name' => $user->name]) }}
        </h1>

        @if ($profile && ! $profile->verified)
            <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-100 text-amber-800 text-sm">
                {{ __('provider.dashboard.pending_banner') }}
            </div>
        @endif

        <div class="mt-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $stats = [
                    ['label' => __('provider.dashboard.stat_received'), 'value' => 0],
                    ['label' => __('provider.dashboard.stat_accepted'), 'value' => 0],
                    ['label' => __('provider.dashboard.stat_credits'), 'value' => $profile->credit_balance ?? 0],
                    ['label' => __('provider.dashboard.stat_rate'), 'value' => '0%'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="p-5 rounded-2xl bg-white border border-emerald-100/60 shadow-sm">
                    <p class="text-xs uppercase tracking-wider text-gray-500">{{ $stat['label'] }}</p>
                    <p class="mt-1 font-display text-3xl font-bold text-brand">{{ $stat['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 p-10 rounded-2xl bg-white border border-emerald-100/60 shadow-sm text-center">
            <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center text-3xl">📥</div>
            <h2 class="mt-4 font-display text-lg font-semibold text-gray-900">{{ __('provider.dashboard.empty_title') }}</h2>
            <p class="mt-2 text-sm text-gray-500">{{ __('provider.dashboard.empty_sub') }}</p>

            @if (! $profile || ! $profile->onboarding_completed)
                <a href="{{ route('provider.profile.edit') }}" class="mt-5 inline-block px-5 py-2.5 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm">
                    {{ __('provider.dashboard.complete_profile_cta') }}
                </a>
            @endif
        </div>
    </div>
@endsection
