@extends('layouts.provider')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-8 sm:p-10 text-center space-y-6">
            <div class="mx-auto w-16 h-16 rounded-full bg-red-50 flex items-center justify-center text-3xl">⚠️</div>

            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">{{ __('credits.failure_title') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('credits.failure_sub') }}</p>
            </div>

            @if ($purchase && $purchase->failure_reason)
                <div class="p-3 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    <span class="text-gray-500">{{ __('credits.failure_reason') }} :</span>
                    <span class="font-mono text-gray-800">{{ $purchase->failure_reason }}</span>
                </div>
            @endif

            <ul class="text-sm text-gray-600 text-left space-y-2 bg-emerald-50/30 border border-emerald-100 rounded-xl p-4">
                @foreach (__('credits.failure_tips') as $tip)
                    <li>• {{ $tip }}</li>
                @endforeach
            </ul>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('provider.credits.index') }}" class="flex-1 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">
                    {{ __('credits.failure_retry') }}
                </a>
                <a href="mailto:support@zawajpro.dz" class="flex-1 py-3 rounded-xl border-2 border-gray-200 hover:border-brand text-brand font-semibold text-sm transition">
                    {{ __('credits.failure_contact') }}
                </a>
            </div>

            @if ($purchase)
                <p class="text-xs text-gray-400 font-mono">{{ $purchase->purchase_uid }}</p>
            @endif
        </div>
    </div>
@endsection
