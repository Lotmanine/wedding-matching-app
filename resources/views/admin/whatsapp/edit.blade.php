@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">WhatsApp</h1>
        <p class="text-sm text-[#6B665E] mt-1 mb-6">Branchez votre compte Meta WhatsApp Cloud API. n8n lit ces valeurs à chaque envoi : aucune modification n'est nécessaire côté n8n.</p>

        {{-- Effective state --}}
        <div @class([
            'mb-6 p-4 rounded-xl border text-sm flex items-start gap-3',
            'bg-green-50 border-green-200 text-green-800' => $isActive,
            'bg-[#F0D9B5]/40 border-[#C8956C]/30 text-[#7A5530]' => ! $isActive,
        ])>
            <x-z.icon :name="$isActive ? 'check_circle' : 'pause_circle'" class="text-xl" />
            <span>
                @if ($isActive)
                    <span class="font-semibold">WhatsApp est actif.</span> Les notifications sont envoyées aux prestataires et aux couples.
                @elseif ($switchedOn && ! $canSend)
                    <span class="font-semibold">WhatsApp est en pause.</span> L'envoi est activé mais le « Phone number ID » ou le jeton d'accès manque.
                @else
                    <span class="font-semibold">WhatsApp est désactivé.</span> Aucun message n'est envoyé ; le reste de la plateforme fonctionne normalement.
                @endif
            </span>
        </div>

        <form method="POST" action="{{ route('admin.whatsapp.update') }}" class="space-y-6">
            @csrf @method('PUT')

            {{-- Switch --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Envoi</p>

                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $switchedOn))
                        class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" />
                    <span>
                        <span class="block font-semibold text-sm text-[#1A1A1A]">Activer l'envoi WhatsApp</span>
                        <span class="block text-xs text-[#9A958D] mt-0.5">Tant que cette case est décochée, n8n ignore les envois sans générer d'erreur.</span>
                    </span>
                </label>
            </div>

            {{-- Meta credentials --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 space-y-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">Meta · Identifiants</p>
                <p class="text-xs text-[#9A958D] -mt-2">Les secrets sont stockés chiffrés. Laissez un champ vide pour conserver la valeur actuelle.</p>

                <div>
                    <label for="wa-phone_number_id" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">Phone number ID</label>
                    <input type="text" id="wa-phone_number_id" name="phone_number_id" inputmode="numeric"
                           value="{{ old('phone_number_id', $phoneNumberId) }}" placeholder="Ex. 123456789012345"
                           class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                    <p class="mt-1 text-[11px] text-[#9A958D]">WhatsApp Manager → API Setup. Identifiant numérique, pas le numéro de téléphone.</p>
                    @error('phone_number_id') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>

                @php
                    $secretFields = [
                        'access_token' => ['Jeton d\'accès', 'Jeton permanent d\'un System User (pas le jeton temporaire de 24 h).'],
                        'verify_token' => ['Verify token', 'Chaîne de votre choix, à saisir à l\'identique dans Meta lors de la configuration du webhook.'],
                        'app_secret'   => ['App secret', 'App Dashboard → Settings → Basic. Sert à vérifier la signature des événements reçus de Meta.'],
                    ];
                @endphp

                @foreach ($secretFields as $field => [$label, $help])
                    @php $s = $secrets[$field] ?? ['set' => false, 'masked' => '']; @endphp
                    <div>
                        <label for="wa-{{ $field }}" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ $label }}</label>
                        <input type="password" id="wa-{{ $field }}" name="{{ $field }}" value="" autocomplete="new-password"
                               placeholder="{{ $s['set'] ? 'Actuel : ' . $s['masked'] : 'Non configuré' }}"
                               class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        <p class="mt-1 text-[11px] text-[#9A958D]">{{ $help }}</p>
                        @error($field) <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <x-z.btn type="submit" variant="accent">Enregistrer</x-z.btn>
            </div>
        </form>
    </div>
@endsection
