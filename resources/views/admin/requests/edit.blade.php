@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <nav class="text-sm text-[#9A958D] mb-3">
            <a href="{{ route('admin.requests.show', $wr->wed_uid) }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors">
                <x-z.icon name="arrow_back" class="text-base" /> {{ $wr->wed_uid }}
            </a>
        </nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">Modifier la demande</h1>
        <p class="text-sm text-[#6B665E] mb-6">Corrigez les informations du couple ou ajustez les critères de la demande.</p>

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

        <form method="POST" action="{{ route('admin.requests.update', $wr->wed_uid) }}" class="space-y-5 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Prénom du couple</label>
                    <input type="text" name="couple_first_name" value="{{ old('couple_first_name', $wr->couple_first_name) }}" required class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Numéro WhatsApp</label>
                    <input type="text" name="couple_whatsapp" value="{{ old('couple_whatsapp', $wr->couple_whatsapp) }}" required placeholder="+2136XXXXXXXX" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Wilaya</label>
                    <select name="couple_wilaya_code" required class="{{ $inputClass }}">
                        @foreach ($wilayas as $w)
                            <option value="{{ $w->code }}" @selected(old('couple_wilaya_code', $wr->couple_wilaya_code) === $w->code)>{{ $w->code }} · {{ $w->name_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Commune <span class="text-[#9A958D] font-normal">(facultatif)</span></label>
                    <input type="text" name="couple_commune" value="{{ old('couple_commune', $wr->couple_commune) }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Date du mariage</label>
                    <input type="date" name="wedding_date" value="{{ old('wedding_date', $wr->wedding_date?->format('Y-m-d')) }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Saison</label>
                    <select name="wedding_season" class="{{ $inputClass }}">
                        <option value="">Aucune</option>
                        @foreach (['Printemps', 'Été', 'Automne', 'Hiver'] as $s)
                            <option value="{{ $s }}" @selected(old('wedding_season', $wr->wedding_season) === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Nombre d'invités</label>
                    <select name="guest_count" class="{{ $inputClass }}">
                        <option value="">Non renseigné</option>
                        @foreach ($guestCounts as $g)
                            <option value="{{ $g->value }}" @selected(old('guest_count', $wr->guest_count?->value) === $g->value)>{{ $g->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Budget</label>
                    <select name="budget_range" class="{{ $inputClass }}">
                        <option value="">Non renseigné</option>
                        @foreach ($budgetRanges as $b)
                            <option value="{{ $b->value }}" @selected(old('budget_range', $wr->budget_range?->value) === $b->value)>{{ $b->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Description</label>
                <textarea name="description" rows="3" maxlength="500" class="{{ $inputClass }} resize-none">{{ old('description', $wr->description) }}</textarea>
            </div>

            {{-- Services (sync children: ajout = nouvelle sous-demande, retrait = suppression douce) --}}
            <fieldset class="pt-4 border-t border-[#D4CFC7]/40">
                <legend class="text-sm font-semibold text-[#1A1A1A] mb-1">Prestataires recherchés <span class="text-[#C8956C]">*</span></legend>
                <p class="text-xs text-[#9A958D] mb-3">Ajouter un service crée une nouvelle sous-demande (statut « new »). Retirer un service supprime sa sous-demande (impossible si elle a déjà été acceptée).</p>
                @php $currentServices = old('services_needed', (array) $wr->services_needed); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach ($serviceTypes as $svc)
                        @php $locked = in_array($svc->value, $lockedServices, true); @endphp
                        <label @class([
                            'flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium transition-colors',
                            'cursor-not-allowed opacity-70 border-[#C8956C]/40 bg-[#F0D9B5]/30 text-[#7A5530]' => $locked,
                            'cursor-pointer border-[#D4CFC7] bg-white text-[#6B665E] hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:text-[#1A1A1A]' => ! $locked,
                        ])>
                            @if ($locked)
                                {{-- accepted child: always submitted, not editable --}}
                                <input type="hidden" name="services_needed[]" value="{{ $svc->value }}" />
                                <input type="checkbox" checked disabled class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C]" />
                            @else
                                <input type="checkbox" name="services_needed[]" value="{{ $svc->value }}"
                                       @checked(in_array($svc->value, $currentServices, true))
                                       class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            @endif
                            <span>{{ $svc->label() }}</span>
                            @if ($locked)<x-z.icon name="lock" class="text-xs ml-auto" />@endif
                        </label>
                    @endforeach
                </div>
                @error('services_needed') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Status + expiration --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#D4CFC7]/40">
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Statut de la demande <span class="text-[#C8956C]">*</span></label>
                    <select name="status" required class="{{ $inputClass }}">
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" @selected(old('status', $wr->status?->value ?? $wr->status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">Expiration <span class="text-[#9A958D] font-normal">(vide = inchangée)</span></label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $wr->expires_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }}" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.requests.show', $wr->wed_uid) }}" class="text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors">Annuler</a>
                <x-z.btn type="submit" variant="accent">Enregistrer</x-z.btn>
            </div>
        </form>
    </div>
@endsection
