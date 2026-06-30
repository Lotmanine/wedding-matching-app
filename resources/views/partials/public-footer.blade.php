{{-- Public footer — clay/ink identity (ported from claude-design PublicFooter) --}}
<footer class="bg-[#1A1A1A] pt-14 pb-8">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10 pb-12 border-b border-white/10">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-white mb-3 hover:text-[#C8956C] transition-colors block">{{ __('form.brand') }}</a>
                <p class="text-sm text-white/40 leading-relaxed mb-5 max-w-[200px]">La plateforme de référence pour les mariages algériens.</p>
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
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">Couples</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('couple.request.create') }}" class="text-sm text-white/55 hover:text-white transition-colors">Soumettre une demande</a></li>
                    <li><a href="{{ route('home') }}#comment" class="text-sm text-white/55 hover:text-white transition-colors">Comment ça marche</a></li>
                    <li><a href="{{ route('home') }}#faq" class="text-sm text-white/55 hover:text-white transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Prestataires --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">Prestataires</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('provider.register') }}" class="text-sm text-white/55 hover:text-white transition-colors">Devenir prestataire</a></li>
                    <li><a href="{{ route('provider.login') }}" class="text-sm text-white/55 hover:text-white transition-colors">Se connecter</a></li>
                    <li><a href="{{ route('provider.register') }}#tarifs" class="text-sm text-white/55 hover:text-white transition-colors">Tarifs crédits</a></li>
                </ul>
            </div>

            {{-- Entreprise --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">Entreprise</p>
                <ul class="space-y-2.5">
                    <li><a href="mailto:contact@zawajpro.dz" class="text-sm text-white/55 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            {{-- Légal --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">Légal</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('legal.privacy') }}" class="text-sm text-white/55 hover:text-white transition-colors">Politique de confidentialité</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-sm text-white/55 hover:text-white transition-colors">Conditions d'utilisation</a></li>
                    <li><a href="{{ route('legal.privacy') }}#anpdp" class="text-sm text-white/55 hover:text-white transition-colors">Conformité ANPDP</a></li>
                </ul>
            </div>
        </div>

        <div class="pt-7 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-white/25">© {{ date('Y') }} {{ __('form.brand') }} · Alger, Algérie · Tous droits réservés</p>
            <div class="flex items-center gap-4">
                <span class="text-xs text-white/25 flex items-center gap-1.5">
                    <x-z.icon name="verified_user" class="text-[#C8956C] text-sm" /> Conforme ANPDP 18-07
                </span>
                <span class="text-xs text-white/25">🇩🇿 69 wilayas</span>
            </div>
        </div>
    </div>
</footer>
