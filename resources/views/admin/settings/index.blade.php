@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.settings.title') }}</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">{{ __('admin.settings.sub') }}</p>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
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
                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 flex items-start justify-between gap-5">
                    <div class="flex-1 min-w-0">
                        <label class="block font-semibold text-sm text-[#1A1A1A]" for="setting-{{ $field }}">{{ $label }}</label>
                        <p class="text-xs text-[#9A958D] mt-0.5 font-mono">{{ $settingKey }}</p>
                    </div>
                    <div class="shrink-0">
                        <input type="number" id="setting-{{ $field }}" name="{{ $field }}"
                               value="{{ old($field, $settings[$settingKey]?->value ?? '') }}"
                               class="w-28 px-3 py-2.5 text-center bg-white border border-[#D4CFC7] rounded-lg text-sm font-bold text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                    </div>
                </div>
            @endforeach

            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                <label class="block font-semibold text-sm text-[#1A1A1A]" for="setting-feedback_alert_email">E-mail d'alerte feedback</label>
                <p class="text-xs text-[#9A958D] mt-0.5 font-mono">feedback.alert_email</p>
                <p class="text-xs text-[#9A958D] mt-1 mb-3">Adresse qui reçoit un e-mail à chaque feedback envoyé par un prestataire. Laissez vide pour ne rien envoyer.</p>
                <input type="email" id="setting-feedback_alert_email" name="feedback_alert_email"
                       value="{{ old('feedback_alert_email', $settings->get('feedback.alert_email')?->value ?? '') }}" placeholder="vous@exemple.com"
                       class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
            </div>

            <div class="flex justify-end pt-2">
                <x-z.btn type="submit" variant="accent">{{ __('admin.settings.save_btn') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
