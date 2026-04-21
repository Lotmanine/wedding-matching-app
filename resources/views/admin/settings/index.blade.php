@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-display text-2xl font-bold">{{ __('admin.settings.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1 mb-5">{{ __('admin.settings.sub') }}</p>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @csrf @method('PUT')

            @php
                $fieldMap = [
                    'credits_unlock_cost'            => 'credits.unlock_cost',
                    'matching_round_timeout_minutes' => 'matching.round_timeout_minutes',
                    'matching_providers_per_round'   => 'matching.providers_per_round',
                    'anpdp_retention_days'           => 'anpdp.retention_days',
                ];
            @endphp

            @foreach (__('admin.settings.labels') as $settingKey => $label)
                @php $field = array_search($settingKey, $fieldMap, true) ?: $settingKey; @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                    <input type="number" name="{{ $field }}"
                           value="{{ old($field, $settings[$settingKey]?->value ?? '') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:border-brand focus:outline-none" />
                    <p class="text-xs text-gray-400 mt-1 font-mono">{{ $settingKey }}</p>
                </div>
            @endforeach

            <div class="pt-3 border-t border-gray-100">
                <button class="px-5 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">{{ __('admin.settings.save_btn') }}</button>
            </div>
        </form>
    </div>
@endsection
