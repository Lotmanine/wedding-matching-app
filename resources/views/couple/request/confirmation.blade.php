@extends('layouts.couple')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-6 sm:p-10 text-center">

            {{-- Success checkmark --}}
            <div class="mx-auto w-20 h-20 rounded-full bg-emerald-50 flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 6 9 17l-5-5"></path>
                </svg>
            </div>

            <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                {{ __('form.confirm_title', ['name' => e($wedRequest->couple_first_name)]) }}
            </h1>
            <p class="text-gray-500 mt-3">{{ __('form.confirm_subtitle') }}</p>

            {{-- Reference UID --}}
            <div class="mt-8 p-5 rounded-xl bg-emerald-50/60 border border-emerald-100 text-left sm:text-center" x-data="{ copied: false }">
                <p class="text-xs uppercase tracking-widest text-brand font-semibold">{{ __('form.confirm_reference') }}</p>
                <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <code class="font-display text-xl sm:text-2xl font-bold text-gray-900 tracking-wide select-all">{{ $wedRequest->wed_uid }}</code>
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText(@js($wedRequest->wed_uid)); copied = true; setTimeout(() => copied = false, 2000)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-emerald-200 hover:border-brand text-brand transition"
                    >
                        <span x-show="!copied">{{ __('form.btn_copy') }}</span>
                        <span x-show="copied" x-cloak>✓ {{ __('form.btn_copied') }}</span>
                    </button>
                </div>
            </div>

            {{-- Next steps --}}
            <section class="mt-10 text-left">
                <h2 class="font-display text-lg font-semibold text-gray-900 mb-4 text-center">{{ __('form.confirm_next_steps') }}</h2>
                <ol class="space-y-4">
                    @foreach ([__('form.confirm_step_1'), __('form.confirm_step_2'), __('form.confirm_step_3')] as $i => $line)
                        <li class="flex gap-4 items-start">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand text-white text-sm font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <p class="text-gray-700 text-sm pt-1">{{ $line }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Services requested --}}
            <section class="mt-10 text-left">
                <h2 class="font-display text-lg font-semibold text-gray-900 mb-4 text-center">{{ __('form.confirm_services') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($wedRequest->serviceRequests as $sr)
                        <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-emerald-100 bg-emerald-50/30">
                            <span class="text-sm font-medium text-gray-800">{{ $sr->service_type?->label() ?? $sr->service_type }}</span>
                            <span class="text-xs text-brand font-medium">{{ __('form.confirm_status_searching') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Expiry notice --}}
            <div class="mt-10 p-4 rounded-xl bg-amber-50 border border-amber-100 text-xs text-amber-800">
                ⏳ {{ __('form.confirm_expiry', ['date' => $wedRequest->expires_at->isoFormat('LL')]) }}
            </div>

            <a href="{{ route('home') }}" class="inline-block mt-8 px-6 py-3 rounded-xl border-2 border-emerald-200 hover:border-brand text-brand font-semibold text-sm transition">
                {{ __('form.btn_home') }}
            </a>

        </div>
    </div>
@endsection
