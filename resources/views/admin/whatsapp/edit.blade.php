@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.nav.whatsapp') }}</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">{{ __('Connect your Meta WhatsApp Cloud API account. n8n reads these values on every send: nothing needs changing on the n8n side.') }}</p>

        {{-- Effective state --}}
        <div @class([
            'mb-6 p-4 rounded-xl border text-sm flex items-start gap-3',
            'bg-green-50 border-green-200 text-green-800' => $isActive,
            'bg-[#F0D9B5]/40 border-[#C8956C]/30 text-[#7A5530]' => ! $isActive,
        ])>
            <x-z.icon :name="$isActive ? 'check_circle' : 'pause_circle'" class="text-xl" />
            <span>
                @if ($isActive)
                    <span class="font-semibold">{{ __('WhatsApp is active.') }}</span> {{ __('Notifications are sent to providers and couples.') }}
                @elseif ($switchedOn && ! $canSend)
                    <span class="font-semibold">{{ __('WhatsApp is paused.') }}</span> {{ __('Sending is on, but the Phone number ID or the access token is missing.') }}
                @else
                    <span class="font-semibold">{{ __('WhatsApp is disabled.') }}</span> {{ __('No messages are sent; the rest of the platform works normally.') }}
                @endif
            </span>
        </div>

        <form method="POST" action="{{ route('admin.whatsapp.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- Switch --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Sending') }}</p>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $switchedOn))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">{{ __('Turn on WhatsApp sending') }}</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">{{ __('While this box is unchecked, n8n skips sending without raising an error.') }}</span>
                    </span>
                </label>
            </div>

            {{-- Meta credentials --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Meta · Credentials') }}</p>
                <p class="text-xs text-[#9A958D] -mt-2">{{ __('Secrets are stored encrypted. Leave a field empty to keep the current value.') }}</p>

                <div>
                    <label for="wa-phone_number_id" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ __('Phone number ID') }}</label>
                    <input type="text" id="wa-phone_number_id" name="phone_number_id" inputmode="numeric"
                           value="{{ old('phone_number_id', $phoneNumberId) }}" placeholder="{{ __('E.g. 123456789012345') }}"
                           class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                    <p class="mt-1 text-[11px] text-[#9A958D]">{{ __('WhatsApp Manager → API Setup. A numeric ID, not the phone number.') }}</p>
                    @error('phone_number_id') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>

                @php
                    $secretFields = [
                        'access_token' => [__('Access token'), __('Permanent token of a System User (not the temporary 24-hour token).')],
                        'verify_token' => [__('Verify token'), __('A string of your choice, entered exactly the same in Meta when setting up the webhook.')],
                        'app_secret'   => [__('App secret'), __('App Dashboard → Settings → Basic. Used to verify the signature of events received from Meta.')],
                    ];
                @endphp

                @foreach ($secretFields as $field => [$label, $help])
                    @php $s = $secrets[$field] ?? ['set' => false, 'masked' => '']; @endphp
                    <div>
                        <label for="wa-{{ $field }}" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ $label }}</label>
                        <input type="password" id="wa-{{ $field }}" name="{{ $field }}" value="" autocomplete="new-password"
                               placeholder="{{ $s['set'] ? __('Current value: :value', ['value' => $s['masked']]) : __('Not configured') }}"
                               class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        <p class="mt-1 text-[11px] text-[#9A958D]">{{ $help }}</p>
                        @error($field) <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <x-z.btn type="submit" variant="accent">{{ __('Save') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
