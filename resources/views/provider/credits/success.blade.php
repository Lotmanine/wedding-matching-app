@extends('layouts.provider')

@section('content')
    <div class="max-w-2xl mx-auto"
         @if ($purchase->status === 'pending') x-data x-init="setTimeout(() => window.location.reload(), 3000)" @endif
    >
        @if ($purchase->status === 'paid')
            <div class="bg-white rounded-2xl border border-emerald-100/70 shadow-lg p-8 sm:p-10 text-center space-y-6">
                <div class="mx-auto w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="w-12 h-12 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 6 9 17l-5-5"></path>
                    </svg>
                </div>

                <div>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ __('credits.success_title') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('credits.success_sub') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4 p-5 rounded-xl bg-emerald-50/60 border border-emerald-100 text-left">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ $purchase->unlockPack?->name }}</p>
                        <p class="font-display text-2xl font-bold text-brand">+{{ $purchase->unlocks_count }}</p>
                        <p class="text-xs text-gray-500">crédits</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Solde total</p>
                        <p class="font-display text-2xl font-bold text-brand">{{ $profile?->credit_balance ?? 0 }}</p>
                        <p class="text-xs text-gray-500">crédits</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('provider.leads.index') }}" class="block w-full py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">
                        {{ __('credits.success_cta') }} →
                    </a>
                    <a href="{{ route('provider.credits.purchase.show', $purchase->purchase_uid) }}?format=pdf" class="block w-full py-3 rounded-xl border-2 border-emerald-200 hover:border-brand text-brand font-semibold text-sm transition">
                        📄 {{ __('credits.success_pdf') }}
                    </a>
                </div>

                <p class="text-xs text-gray-400 font-mono">{{ $purchase->purchase_uid }}</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-amber-100 shadow-sm p-8 sm:p-10 text-center space-y-4">
                <div class="mx-auto w-16 h-16 rounded-full bg-amber-50 flex items-center justify-center text-3xl">⏳</div>
                <h1 class="font-display text-xl font-bold text-gray-900">{{ __('credits.success_pending_title') }}</h1>
                <p class="text-sm text-gray-600">{{ __('credits.success_pending_sub') }}</p>
                <p class="text-xs text-gray-400 font-mono">{{ $purchase->purchase_uid }}</p>
            </div>
        @endif
    </div>
@endsection
