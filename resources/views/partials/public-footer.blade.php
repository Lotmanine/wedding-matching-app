{{-- Public footer — clay/ink identity (ported from claude-design PublicFooter) --}}
@php
    $footerIsArabic = app()->getLocale() === 'ar';
    $footerWilayas = \App\Models\Wilaya::active()->pluck($footerIsArabic ? 'name_ar' : 'name_fr');
    $footerWilayasText = $footerIsArabic ? $footerWilayas->join('، ', ' و') : $footerWilayas->join(', ', ' et ');
@endphp
<footer class="bg-[#1A1A1A] pt-14 pb-8">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10 pb-12 border-b border-white/10">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-white mb-3 hover:text-[#C8956C] transition-colors block">{{ __('form.brand') }}</a>
                <p class="text-sm text-white/40 leading-relaxed mb-5 max-w-[200px]">{{ __('Plateforme de mise en relation entre couples et prestataires de mariage en Algérie.') }}</p>
                <div class="flex gap-3">
                    @foreach (['facebook', 'camera_alt', 'alternate_email'] as $i)
                        <span class="w-8 h-8 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center">
                            <x-z.icon :name="$i" class="text-white/50 text-base" />
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Couples --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Couples') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('couple.request.create') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Soumettre une demande') }}</a></li>
                    <li><a href="{{ route('home') }}#comment" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Comment ça marche') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('FAQ') }}</a></li>
                </ul>
            </div>

            {{-- Prestataires --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Prestataires') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('provider.landing') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Devenir prestataire') }}</a></li>
                    <li><a href="{{ route('provider.login') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Se connecter') }}</a></li>
                    <li><a href="{{ route('provider.landing') }}#tarifs" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Tarifs crédits') }}</a></li>
                </ul>
            </div>

            {{-- Entreprise --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Entreprise') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="mailto:contact@zawajpro.dz" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Contact') }}</a></li>
                    <li><a href="{{ route('admin.login') }}" class="text-sm text-white/40 hover:text-white/70 transition-colors inline-flex items-center gap-1.5"><x-z.icon name="shield_person" class="text-sm" />{{ __('Administration') }}</a></li>
                </ul>
            </div>

            {{-- Légal --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Légal') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('legal.privacy') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Politique de confidentialité') }}</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __("Conditions d'utilisation") }}</a></li>
                    <li><a href="{{ route('legal.privacy') }}#anpdp" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Protection des données personnelles') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-7 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-white/25">© {{ date('Y') }} {{ __('form.brand') }} · {{ __('Alger, Algérie') }} · {{ __('Tous droits réservés') }}</p>
            <div class="flex items-center gap-4">
                <span class="text-xs text-white/25 flex items-center gap-1.5">
                    <x-z.icon name="shield" class="text-[#C8956C] text-sm" /> {{ __('Protection des données personnelles') }}
                </span>
                <span class="text-xs text-white/25">🇩🇿 {{ __(':wilayas · autres wilayas bientôt', ['wilayas' => $footerWilayasText]) }}</span>
            </div>
        </div>
    </div>
</footer>
