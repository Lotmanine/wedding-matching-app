{{--
    Payment-method choice page. Reached only by a logged-in, verified provider
    from the credits page. Shows the enabled methods:
      - online_chargily  : redirect to Chargily (Edahabia / CIB)
      - offline_versement : declare a bank/CCP transfer for admin verification
--}}
@extends('layouts.provider')

@section('content')
    @php
        $perUnlock = $pack->unlocks_count > 0 ? (int) round($pack->price_dzd / $pack->unlocks_count) : 0;
        $hasOnline = in_array(\App\Services\Payments\PaymentSettings::METHOD_ONLINE, $methods, true);
        $hasOffline = in_array(\App\Services\Payments\PaymentSettings::METHOD_OFFLINE, $methods, true);
    @endphp

    <div class="max-w-3xl mx-auto px-5 md:px-8 py-8 md:py-12" x-data="{ tab: '{{ $hasOnline ? 'online' : 'offline' }}' }">

        <a href="{{ route('provider.credits.index') }}" class="inline-flex items-center gap-1 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors mb-6">
            <x-z.icon name="arrow_back" class="text-base" /> Retour aux crédits
        </a>

        {{-- Pack summary --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 mb-6 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">{{ $pack->name }}</p>
                <p class="font-serif text-3xl font-bold text-[#1A1A1A]">{{ $pack->unlocks_count }} <span class="text-lg font-sans font-medium text-[#6B665E]">crédits</span></p>
            </div>
            <div class="text-right">
                <p class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ number_format($pack->price_dzd, 0, ',', ' ') }} DA</p>
                <p class="text-xs text-[#9A958D]">{{ number_format($perUnlock, 0, ',', ' ') }} DA / crédit</p>
            </div>
        </div>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">Choisissez votre mode de paiement</h1>
        <p class="text-[#6B665E] text-sm mb-6">Paiement 100 % sécurisé. Vos crédits sont ajoutés automatiquement (en ligne) ou après vérification (versement).</p>

        {{-- Method tabs (only if both are enabled) --}}
        @if ($hasOnline && $hasOffline)
            <div class="flex items-center gap-1 p-1 bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/50 mb-6 w-fit">
                <button type="button" @click="tab = 'online'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    :class="tab === 'online' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                    <x-z.icon name="credit_card" class="text-base" /> Paiement en ligne
                </button>
                <button type="button" @click="tab = 'offline'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    :class="tab === 'offline' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                    <x-z.icon name="account_balance" class="text-base" /> Versement (CCP / Banque)
                </button>
            </div>
        @endif

        {{-- ── Online (Chargily) ─────────────────────────────── --}}
        @if ($hasOnline)
            <div x-show="tab === 'online'" x-cloak class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-xl bg-[#F0D9B5]/50 flex items-center justify-center shrink-0">
                        <x-z.icon name="credit_card" :filled="true" class="text-[#C8956C] text-2xl" />
                    </div>
                    <div class="flex-1">
                        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">Carte Edahabia / CIB</h2>
                        <p class="text-sm text-[#6B665E] mt-1 mb-5">Paiement instantané et sécurisé via Chargily Pay. Vos crédits sont ajoutés immédiatement après confirmation.</p>
                        <form method="POST" action="{{ route('provider.credits.checkout', $pack->slug) }}">
                            @csrf
                            <x-z.btn type="submit" variant="primary" size="lg" class="w-full sm:w-auto justify-center">
                                Payer {{ number_format($pack->price_dzd, 0, ',', ' ') }} DA <x-z.icon name="arrow_forward" class="text-lg" />
                            </x-z.btn>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Offline (versement CCP / banque) ──────────────── --}}
        @if ($hasOffline)
            <div x-show="tab === 'offline'" x-cloak class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-11 h-11 rounded-xl bg-[#F0D9B5]/50 flex items-center justify-center shrink-0">
                        <x-z.icon name="account_balance" :filled="true" class="text-[#C8956C] text-2xl" />
                    </div>
                    <div>
                        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">Versement bancaire ou CCP</h2>
                        <p class="text-sm text-[#6B665E] mt-1">Effectuez le versement puis déclarez-le ci-dessous. Vos crédits seront ajoutés dès validation par notre équipe.</p>
                    </div>
                </div>

                {{-- Coordinates --}}
                <div class="bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/40 p-5 mb-6 space-y-2.5 text-sm">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1">Coordonnées de paiement</p>
                    @forelse (array_filter([
                        'Banque'            => $offlineDetails['bank_name'] ?? '',
                        'Titulaire'         => $offlineDetails['account_holder'] ?? '',
                        'RIB'               => $offlineDetails['rib'] ?? '',
                        'Compte CCP'        => $offlineDetails['ccp_number'] ?? '',
                        'Clé CCP'           => $offlineDetails['ccp_key'] ?? '',
                    ]) as $label => $value)
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-[#9A958D]">{{ $label }}</span>
                            <span class="font-mono font-semibold text-[#1A1A1A] text-right select-all">{{ $value }}</span>
                        </div>
                    @empty
                        <p class="text-[#9A958D]">Les coordonnées de paiement seront communiquées par notre équipe.</p>
                    @endforelse
                    @if (! empty($offlineDetails['instructions']))
                        <p class="text-xs text-[#6B665E] pt-2 border-t border-[#D4CFC7]/40 mt-2">{{ $offlineDetails['instructions'] }}</p>
                    @endif
                    <p class="flex items-center justify-between gap-4 pt-2 border-t border-[#D4CFC7]/40 mt-2">
                        <span class="text-[#9A958D]">Montant à verser</span>
                        <span class="font-serif text-lg font-bold text-[#C8956C]">{{ number_format($pack->price_dzd, 0, ',', ' ') }} DA</span>
                    </p>
                </div>

                {{-- Declaration form --}}
                <form method="POST" action="{{ route('provider.credits.offline', $pack->slug) }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div>
                        <label for="payment_reference" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">
                            Référence du versement <span class="text-[#C8956C]">*</span>
                        </label>
                        <input type="text" id="payment_reference" name="payment_reference" value="{{ old('payment_reference') }}" required
                            placeholder="N° de bordereau / référence de virement"
                            class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
                        @error('payment_reference') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="proof" class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">
                            Justificatif <span class="text-[#9A958D] font-normal">(facultatif : photo ou PDF, max 5 Mo)</span>
                        </label>
                        <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full text-sm text-[#6B665E] file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#F0D9B5]/60 file:text-[#7A5530] hover:file:bg-[#F0D9B5] cursor-pointer" />
                        @error('proof') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                    <x-z.btn type="submit" variant="accent" size="lg" class="w-full sm:w-auto justify-center">
                        <x-z.icon name="send" class="text-lg" /> Déclarer mon versement
                    </x-z.btn>
                    <p class="text-xs text-[#9A958D]">Votre achat sera en attente de vérification jusqu'à confirmation de la réception des fonds.</p>
                </form>
            </div>
        @endif

    </div>
@endsection
