{{--
    Payment-method choice page. Reached only by a logged-in, verified provider
    from the credits page. Shows the enabled methods:
      - online  : card payment
      - offline : declare a bank transfer for admin verification
--}}
@extends('layouts.provider')

@section('content')
    @php
        $perUnlock = $pack->unlocks_count > 0 ? (int) round($pack->price / $pack->unlocks_count) : 0;
        $currency = auth()->user()?->providerProfile?->currency() ?? \App\Support\Money::currencyForCity(null);
        $hasOnline = in_array(\App\Services\Payments\PaymentSettings::METHOD_ONLINE, $methods, true);
        $hasOffline = in_array(\App\Services\Payments\PaymentSettings::METHOD_OFFLINE, $methods, true);
        // [label, value] pairs: two rows can share a label. The second account
        // number comes from the older number + key fields; its row stays hidden
        // when they are empty.
        $legacyAccount = trim((string) ($offlineDetails['ccp_number'] ?? ''));
        $paymentRows = array_filter([
            [__('Bank'), $offlineDetails['bank_name'] ?? ''],
            [__('Account holder'), $offlineDetails['account_holder'] ?? ''],
            [__('Account number'), $offlineDetails['rib'] ?? ''],
            [__('Account number'), $legacyAccount === '' ? '' : trim($legacyAccount . ' ' . ($offlineDetails['ccp_key'] ?? ''))],
        ], fn (array $row) => $row[1] !== '');
    @endphp

    <div class="max-w-3xl mx-auto px-5 md:px-8 py-8 md:py-12" x-data="{ tab: '{{ $hasOnline ? 'online' : 'offline' }}' }">

        <a href="{{ route('provider.credits.index') }}" class="inline-flex items-center gap-1 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors mb-6">
            <x-z.icon name="arrow_back" class="text-base" /> {{ __('Back to credits') }}
        </a>

        {{-- Pack summary --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 mb-6 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">{{ __($pack->name) }}</p>
                <p class="font-serif text-3xl font-bold text-[#1A1A1A]">{{ trans_choice('credits.count', (int) $pack->unlocks_count, ['count' => (int) $pack->unlocks_count]) }}</p>
            </div>
            <div class="text-end">
                <p class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ \App\Support\Money::format($pack->price, $currency) }}</p>
                <p class="text-xs text-[#9A958D]">{{ __('credits.pack_per_unlock', ['price' => \App\Support\Money::format($perUnlock, $currency)]) }}</p>
            </div>
        </div>

        {{-- The intro only appears when there is a choice to make; each method explains itself in its own panel --}}
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] {{ $hasOnline && $hasOffline ? 'mb-1' : 'mb-6' }}">{{ $hasOnline && $hasOffline ? __('credits.pay.title_choice') : __('credits.pay.title_single') }}</h1>
        @if ($hasOnline && $hasOffline)
            <p class="text-[#6B665E] text-sm mb-6">{{ __('credits.pay.intro_both') }}</p>
        @endif

        {{-- Method tabs (only if both are enabled) --}}
        @if ($hasOnline && $hasOffline)
            <div class="flex items-center gap-1 p-1 bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/50 mb-6 w-fit">
                <button type="button" @click="tab = 'online'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    :class="tab === 'online' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                    <x-z.icon name="credit_card" class="text-base" /> {{ __('Pay by card') }}
                </button>
                <button type="button" @click="tab = 'offline'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    :class="tab === 'offline' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                    <x-z.icon name="account_balance" class="text-base" /> {{ __('Bank transfer') }}
                </button>
            </div>
        @endif

        {{-- ── Online (card) ─────────────────────────────────── --}}
        @if ($hasOnline)
            <div x-show="tab === 'online'" x-cloak class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#F0D9B5]/50 flex items-center justify-center shrink-0">
                        <x-z.icon name="credit_card" :filled="true" class="text-[#C8956C] text-2xl" />
                    </div>
                    <div class="flex-1">
                        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('Pay by card') }}</h2>
                        <p class="text-sm text-[#6B665E] mt-1 mb-5">{{ __('credits.pay.online_body') }}</p>
                        <form method="POST" action="{{ route('provider.credits.checkout', $pack->slug) }}">
                            @csrf
                            <x-z.btn type="submit" variant="primary" size="lg" class="w-full sm:w-auto justify-center">
                                {{ __('Pay :price', ['price' => \App\Support\Money::format($pack->price, $currency)]) }} <x-z.icon name="arrow_forward" class="text-lg" />
                            </x-z.btn>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Offline (bank transfer) ───────────────────────── --}}
        @if ($hasOffline)
            <div x-show="tab === 'offline'" x-cloak class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-11 h-11 rounded-xl bg-[#F0D9B5]/50 flex items-center justify-center shrink-0">
                        <x-z.icon name="account_balance" :filled="true" class="text-[#C8956C] text-2xl" />
                    </div>
                    <div>
                        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('Bank transfer') }}</h2>
                        <p class="text-sm text-[#6B665E] mt-1">{{ __('credits.pay.offline_body') }}</p>
                    </div>
                </div>

                {{-- Coordinates --}}
                <div class="bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/40 p-5 mb-6 space-y-2.5 text-sm">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">{{ __('Payment details') }}</p>
                    @forelse ($paymentRows as $row)
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[#9A958D]">{{ $row[0] }}</span>
                            <span class="font-mono font-semibold text-[#1A1A1A] text-end select-all">{{ $row[1] }}</span>
                        </div>
                    @empty
                        <p class="text-[#9A958D]">{{ __('Our team will send you the payment details.') }}</p>
                    @endforelse
                    {{-- Free text saved by the admin at /admin/payments (setting payments.offline.instructions): shown as stored, never translated --}}
                    @if (! empty($offlineDetails['instructions']))
                        <p class="text-xs text-[#6B665E] pt-2 border-t border-[#D4CFC7]/40 mt-2">{{ $offlineDetails['instructions'] }}</p>
                    @endif
                    <p class="flex items-center justify-between gap-4 pt-2 border-t border-[#D4CFC7]/40 mt-2">
                        <span class="text-[#9A958D]">{{ __('Amount to transfer') }}</span>
                        <span class="font-serif text-lg font-bold text-[#C8956C]">{{ \App\Support\Money::format($pack->price, $currency) }}</span>
                    </p>
                </div>

                {{-- Declaration form --}}
                <form method="POST" action="{{ route('provider.credits.offline', $pack->slug) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label for="payment_reference" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">
                            {{ __('Transfer reference') }} <span class="text-[#C8956C]">*</span>
                        </label>
                        <input type="text" id="payment_reference" name="payment_reference" value="{{ old('payment_reference') }}" required
                            placeholder="{{ __('Slip number or transfer reference') }}"
                            class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
                        @error('payment_reference') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="proof" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">
                            {{ __('Proof of payment (optional)') }}
                        </label>
                        <p id="proof-help" class="text-xs text-[#9A958D] mb-2">{{ __('Photo or PDF, 5 MB maximum.') }}</p>
                        <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf" aria-describedby="proof-help"
                            class="w-full text-sm text-[#6B665E] file:me-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F0D9B5]/60 file:text-[#7A5530] hover:file:bg-[#F0D9B5] cursor-pointer" />
                        @error('proof') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                    <x-z.btn type="submit" variant="accent" size="lg" class="w-full sm:w-auto justify-center">
                        <x-z.icon name="send" class="text-lg" /> {{ __('Report my transfer') }}
                    </x-z.btn>
                </form>
            </div>
        @endif

    </div>
@endsection
