@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl" x-data="{ role: '{{ old('role', 'provider') }}' }">
        <nav class="text-sm text-[#9A958D] mb-3">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors">
                <x-z.icon name="arrow_back" class="text-base" /> {{ __('admin.nav.users') }}
            </a>
        </nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">{{ __('Create a user') }}</h1>
        <p class="text-sm text-[#6B665E] mb-6">{{ __('Back-office account (admin, manager) or provider account (created verified and active).') }}</p>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-100 text-[#A84936] text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @php
            $inputClass = 'w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors';
        @endphp

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
            @csrf

            {{-- Role --}}
            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('Role') }} <span class="text-[#C8956C]">*</span></label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ([
                        'provider' => ['storefront', __('admin.common.provider'), __('Verified and active from creation')],
                        'manager'  => ['support_agent', __('Manager'), __('Operations: requests, round-robin, providers, purchases')],
                        'admin'    => ['shield_person', __('admin.common.admin'), __('Full access, including configuration')],
                    ] as $value => [$icon, $label, $desc])
                        <label class="relative flex flex-col gap-1.5 p-4 rounded-xl border-2 cursor-pointer transition-all border-[#D4CFC7] bg-white hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40">
                            <input type="radio" name="role" value="{{ $value }}" x-model="role" class="sr-only" @checked(old('role', 'provider') === $value) />
                            <span class="flex items-center gap-2 font-semibold text-sm text-[#1A1A1A]">
                                <x-z.icon :name="$icon" class="text-lg text-[#C8956C]" /> {{ $label }}
                            </span>
                            <span class="text-[11px] text-[#9A958D] leading-snug">{{ $desc }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="u-name" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Full name') }} <span class="text-[#C8956C]">*</span></label>
                    <input type="text" id="u-name" name="name" value="{{ old('name') }}" required class="{{ $inputClass }}" />
                </div>
                <div>
                    <label for="u-email" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Email') }} <span class="text-[#C8956C]">*</span></label>
                    <input type="email" id="u-email" name="email" value="{{ old('email') }}" required class="{{ $inputClass }}" />
                </div>
                <div>
                    <label for="u-password" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Password') }} <span class="text-[#9A958D] font-normal">{{ __('(empty = generated automatically)') }}</span></label>
                    <input type="password" id="u-password" name="password" autocomplete="new-password" minlength="10" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label for="u-phone" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Phone / WhatsApp') }} <span class="text-[#9A958D] font-normal">{{ __('(optional)') }}</span></label>
                    <input type="tel" id="u-phone" name="phone" value="{{ old('phone') }}" placeholder="+12125550123" class="{{ $inputClass }}" />
                </div>
            </div>

            {{-- Provider-only fields --}}
            <div x-show="role === 'provider'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#D4CFC7]/40">
                <div>
                    <label for="u-business" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Business name') }} <span class="text-[#C8956C]">*</span></label>
                    <input type="text" id="u-business" name="business_name" value="{{ old('business_name') }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label for="u-city" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Main city') }} <span class="text-[#C8956C]">*</span></label>
                    <select id="u-city" name="primary_city_code" class="{{ $inputClass }}">
                        <option value="">{{ __('Select…') }}</option>
                        @foreach ($cities as $w)
                            <option value="{{ $w->code }}" @selected(old('primary_city_code') === $w->code)>{{ $w->code }} · {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors">{{ __('Cancel') }}</a>
                <x-z.btn type="submit" variant="accent">{{ __('Create user') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
