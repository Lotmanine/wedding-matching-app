@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Payment methods') }}</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">{{ __('Turn on the methods offered to providers and enter the bank transfer details.') }}</p>

        <form method="POST" action="{{ route('admin.payments.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- Method toggles --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Enabled methods') }}</p>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="online_enabled" value="1" @checked(old('online_enabled', $onlineEnabled))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">{{ __('Online payment (Chargily Pay)') }}</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">{{ __('Optional card payment gateway. Credits are added automatically. API keys can be set below.') }}</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="offline_enabled" value="1" @checked(old('offline_enabled', $offlineEnabled))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">{{ __('Bank transfer') }}</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">{{ __('The provider declares a transfer; you confirm receipt to credit the account.') }}</span>
                    </span>
                </label>
            </div>

            {{-- Chargily credentials --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Chargily Pay · API keys') }}</p>
                <p class="text-xs text-[#9A958D] -mt-2">{!! __('Stored encrypted. Leave a field empty to keep the current key. If no key is entered here, the ones in the <span class="font-mono">.env</span> file stay in use.') !!}</p>

                <div>
                    <label class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ __('Mode') }}</label>
                    <div class="flex gap-3">
                        @foreach (['test' => __('Test (sandbox)'), 'live' => __('Live (production)')] as $m => $label)
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border cursor-pointer text-sm font-semibold transition-colors has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 border-[#D4CFC7] text-[#6B665E]">
                                <input type="radio" name="chargily_mode" value="{{ $m }}" @checked(old('chargily_mode', $chargilyMode) === $m)
                                    class="w-3.5 h-3.5 text-[#C8956C] border-[#D4CFC7] focus:ring-[#C8956C]/30" />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @foreach (['chargily_secret_key' => [__('Secret key (API)'), 'secret_key'], 'chargily_webhook_secret' => [__('Webhook secret'), 'webhook_secret']] as $field => [$label, $statusKey])
                    @php $ks = $chargilyKeys[$statusKey] ?? ['source' => 'absent', 'masked' => '']; @endphp
                    <div>
                        <label for="pay-{{ $field }}" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ $label }}</label>
                        <input type="password" id="pay-{{ $field }}" name="{{ $field }}" value="" autocomplete="new-password"
                               placeholder="{{ $ks['masked'] !== '' ? __('Current key: :value', ['value' => $ks['masked']]) : __('No key configured') }}"
                               class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        <p class="mt-1 text-[11px] text-[#9A958D]">
                            @if ($ks['source'] === 'db') {{ __('Current source: database (encrypted)') }}
                            @elseif ($ks['source'] === 'env') {{ __('Current source: .env file') }}
                            @else {{ __('No key configured') }}
                            @endif
                        </p>
                        @error($field) <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            {{-- Offline coordinates --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('Bank transfer details') }}</p>
                <p class="text-xs text-[#9A958D] -mt-2">{{ __('Shown to the provider when they choose bank transfer.') }}</p>

                @php
                    $fields = [
                        'bank_name'      => [__('Bank'), 'text', __('E.g. Example Bank')],
                        'account_holder' => [__('Account holder'), 'text', __('E.g. Example Weddings LLC')],
                        'account_number'            => [__('Account number'), 'text', __('Account number')],
                        'secondary_account_number'     => [__('Second account number'), 'text', __('Second account number')],
                        'secondary_account_key'        => [__('Second account key'), 'text', __('Key')],
                    ];
                @endphp

                @foreach ($fields as $name => [$label, $type, $ph])
                    <div>
                        <label for="pay-{{ $name }}" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ $label }}</label>
                        <input type="{{ $type }}" id="pay-{{ $name }}" name="{{ $name }}"
                               value="{{ old($name, $details[$name] ?? '') }}" placeholder="{{ $ph }}"
                               class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        @error($name) <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                <div>
                    <label for="pay-instructions" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ __('Additional instructions') }}</label>
                    <textarea id="pay-instructions" name="instructions" rows="3" placeholder="{{ __('E.g. Put your name in the transfer reference.') }}"
                              class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors resize-none">{{ old('instructions', $details['instructions'] ?? '') }}</textarea>
                    @error('instructions') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <x-z.btn type="submit" variant="accent">{{ __('Save') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
