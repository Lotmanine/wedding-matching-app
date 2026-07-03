@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">Modes de paiement</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">Activez les méthodes proposées aux prestataires et renseignez les coordonnées de versement.</p>

        <form method="POST" action="{{ route('admin.payments.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- Method toggles --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Méthodes activées</p>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="online_enabled" value="1" @checked(old('online_enabled', $onlineEnabled))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">Paiement en ligne (Chargily Pay)</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">Carte Edahabia / CIB. Crédits ajoutés automatiquement. Clés API configurables ci-dessous.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="offline_enabled" value="1" @checked(old('offline_enabled', $offlineEnabled))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">Versement (CCP / Banque)</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">Le prestataire déclare un virement ; vous confirmez la réception pour créditer le compte.</span>
                    </span>
                </label>
            </div>

            {{-- Chargily credentials --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Chargily Pay · Clés API</p>
                <p class="text-xs text-[#9A958D] -mt-2">Stockées chiffrées. Laissez un champ vide pour conserver la clé actuelle. Si aucune clé n'est saisie ici, celles du fichier <span class="font-mono">.env</span> restent utilisées.</p>

                <div>
                    <label class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">Mode</label>
                    <div class="flex gap-3">
                        @foreach (['test' => 'Test (sandbox)', 'live' => 'Live (production)'] as $m => $label)
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border cursor-pointer text-sm font-semibold transition-colors has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 border-[#D4CFC7] text-[#6B665E]">
                                <input type="radio" name="chargily_mode" value="{{ $m }}" @checked(old('chargily_mode', $chargilyMode) === $m)
                                    class="w-3.5 h-3.5 text-[#C8956C] border-[#D4CFC7] focus:ring-[#C8956C]/30" />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                @foreach (['chargily_secret_key' => ['Clé secrète (API)', 'secret_key'], 'chargily_webhook_secret' => ['Secret webhook', 'webhook_secret']] as $field => [$label, $statusKey])
                    @php $ks = $chargilyKeys[$statusKey] ?? ['source' => 'absent', 'masked' => '']; @endphp
                    <div>
                        <label for="pay-{{ $field }}" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ $label }}</label>
                        <input type="password" id="pay-{{ $field }}" name="{{ $field }}" value="" autocomplete="new-password"
                               placeholder="{{ $ks['masked'] !== '' ? 'Actuelle : ' . $ks['masked'] : 'Non configurée' }}"
                               class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        <p class="mt-1 text-[11px] text-[#9A958D]">
                            @if ($ks['source'] === 'db') Source actuelle : base de données (chiffrée)
                            @elseif ($ks['source'] === 'env') Source actuelle : fichier .env
                            @else Aucune clé configurée
                            @endif
                        </p>
                        @error($field) <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            {{-- Offline coordinates --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Coordonnées de versement</p>
                <p class="text-xs text-[#9A958D] -mt-2">Affichées au prestataire lorsqu'il choisit le versement.</p>

                @php
                    $fields = [
                        'bank_name'      => ['Banque', 'text', 'Ex. BNA, CPA…'],
                        'account_holder' => ['Titulaire du compte', 'text', 'Ex. Zawaj Pro SARL'],
                        'rib'            => ['RIB', 'text', '20 chiffres'],
                        'ccp_number'     => ['Compte CCP', 'text', 'N° de compte CCP'],
                        'ccp_key'        => ['Clé CCP', 'text', 'Clé'],
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
                    <label for="pay-instructions" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">Instructions complémentaires</label>
                    <textarea id="pay-instructions" name="instructions" rows="3" placeholder="Ex. Mentionnez votre nom en référence du virement."
                              class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors resize-none">{{ old('instructions', $details['instructions'] ?? '') }}</textarea>
                    @error('instructions') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <x-z.btn type="submit" variant="accent">Enregistrer</x-z.btn>
            </div>
        </form>
    </div>
@endsection
