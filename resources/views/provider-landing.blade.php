{{--
    ═══════════════════════════════════════════════════════
    ZAWAJ PRO — Landing prestataires ("Devenez prestataire")
    Porté depuis claude-design/ProviderLanding.jsx. Vend la
    plateforme aux prestataires ; les CTA mènent au formulaire
    d'inscription. Les tarifs lisent les UnlockPack actifs réels.
    ═══════════════════════════════════════════════════════
--}}
@extends('layouts.couple')

@section('title', __('Pour les prestataires · Zawaj Pro'))

@section('content')
@php
    // Figures shown on this page come from the real configuration, never from
    // marketing estimates: what we print here is what the platform does.
    $roundHours   = max(1, intdiv((int) config('wedding.matching.round_timeout_minutes', 120), 60));
    $perRound     = (int) config('wedding.matching.providers_per_round', 3);
    $maxRounds    = (int) config('wedding.matching.max_rounds', 3);
    $validityDays = (int) config('wedding.privacy.retention_days', 14);
    $unlockCost   = max(1, (int) config('wedding.credits.unlock_cost', 1));
    $openCities   = \App\Models\City::active()->pluck('name');
    $openCitiesText = $openCities->join(', ', ' et ');
    $currency     = \App\Support\Money::currencyForCity(null);

    // Buttons that open registration: /provider/register is guest-only, so a
    // signed-in visitor is redirected to their own space. Label accordingly.
    $isSignedIn = auth()->check();
    $ctaLabel   = $isSignedIn ? __('Tableau de bord') : __('Créer mon compte');

    // Services follow the active catalog (same source as provider registration).
    $serviceIcons = [
        'photo'      => 'camera_alt',
        'video'      => 'videocam',
        'catering'   => 'restaurant',
        'decoration' => 'local_florist',
        'pastry' => 'cake',
        'venue'      => 'villa',
        'music'    => 'music_note',
        'beauty'     => 'face_retouching_natural',
    ];
    $services = array_map(
        fn (\App\Enums\ServiceType $svc) => ['icon' => $serviceIcons[$svc->value] ?? 'check_circle', 'label' => $svc->label()],
        \App\Enums\ServiceType::activeCases(),
    );

    $noGuarantee = __("L'acceptation ne garantit ni réponse ni réservation.");
    $costLine    = trans_choice('credits.unlock_cost_line', $unlockCost, ['count' => $unlockCost]);

    $steps = [
        ['n' => '01', 'icon' => 'inbox',      'title' => __('Recevez des demandes'), 'body' => __("Elles correspondent à vos services et à vos wilayas d'intervention. Vérifiez votre disponibilité à la date du mariage.")],
        ['n' => '02', 'icon' => 'visibility', 'title' => __('Choisissez une demande'), 'body' => __('Consultez gratuitement le service, le lieu, la date, le budget et les précisions disponibles.')],
        ['n' => '03', 'icon' => 'chat',       'title' => __('Contactez le couple'), 'body' => __('Acceptez au coût indiqué pour voir le prénom et le numéro WhatsApp. Convenez du prix directement avec le couple.') . ' ' . $noGuarantee],
    ];

    $faqs = [
        ['q' => __('Comment recevoir des demandes ?'), 'a' => __("Vous recevez dans votre tableau de bord les demandes qui correspondent à vos services et à vos wilayas d'intervention. Vous consultez leurs détails gratuitement. La plateforme ne connaît pas votre agenda : vérifiez la date du mariage avant d'accepter.")],
        ['q' => __('À quoi servent les crédits ?'), 'a' => __("Les crédits s'achètent par packs. Accepter une demande utilise le coût affiché et donne accès au prénom et au numéro WhatsApp. Cela ne garantit ni réponse ni réservation.") . ' ' . $costLine],
        ['q' => __('Puis-je refuser une demande ?'), 'a' => __('Oui. Vous choisissez les demandes à accepter. Refuser ne coûte aucun crédit.')],
        ['q' => __('Comment mon profil est-il vérifié ?'), 'a' => __("Après votre inscription, notre équipe examine votre profil avant de vous donner accès aux demandes. Tant que votre profil n'est pas validé, vous ne recevez pas de demandes.")],
        ['q' => __('Y a-t-il un abonnement ?'), 'a' => __('Non. Vous achetez des crédits selon vos besoins, sans frais récurrents.')],
    ];

@endphp

<div class="-mx-4 sm:-mx-6 -my-8 sm:-my-12 bg-[#FDFAF6]">

    {{-- ── Hero ──────────────────────────────────────────── --}}
    <section class="pt-16 md:pt-24 px-5 md:px-8 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(ellipse at 70% 0%, #F0D9B5 0%, transparent 55%)"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-20 items-center pb-16 relative">
            {{-- Left: text --}}
            <div>
                <x-z.badge variant="dark" class="mb-6">
                    <x-z.icon name="storefront" class="text-sm" /> {{ __('Disponible à :cities', ['cities' => $openCitiesText]) }}
                </x-z.badge>
                <h1 class="font-serif text-5xl md:text-6xl lg:text-[68px] text-[#1A1A1A] font-bold leading-[1.05] mb-6 text-balance">
                    {{ __('Recevez des demandes de mariage') }}
                </h1>
                <p class="text-lg md:text-xl text-[#6B665E] leading-relaxed mb-10 max-w-xl">
                    {{ __('Des demandes pour vos services, dans les wilayas où vous travaillez. Consultez les détails gratuitement et choisissez celles à accepter.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <x-z.btn :href="route('provider.register')" size="lg" variant="accent" class="shadow-xl shadow-[#C8956C]/20">
                        {{ $ctaLabel }} <x-z.icon name="arrow_forward" class="text-lg" />
                    </x-z.btn>
                    @guest
                        <x-z.btn :href="route('provider.login')" size="lg" variant="outline">
                            {{ __('Se connecter') }}
                        </x-z.btn>
                    @endguest
                </div>
                <p class="text-xs text-[#9A958D] mt-5 flex items-center gap-2">
                    <x-z.icon name="verified" :filled="true" class="text-[#C8956C] text-base" /> {{ __('Inscription gratuite · Profil examiné par notre équipe · Sans abonnement') }}
                </p>
            </div>

            {{-- Right: WhatsApp / Dashboard preview switcher (Alpine) --}}
            <div class="hidden lg:flex flex-col items-center" x-data="{ heroTab: 'whatsapp' }">
                {{-- Tab switcher --}}
                <div class="flex items-center gap-1 p-1 bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/50 mb-6 self-start">
                    <button type="button" @click="heroTab = 'whatsapp'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="heroTab === 'whatsapp' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                        <x-z.icon name="chat" class="text-base" /> {{ __('WhatsApp') }}
                    </button>
                    <button type="button" @click="heroTab = 'dashboard'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="heroTab === 'dashboard' ? 'bg-white text-[#1A1A1A] shadow-sm' : 'text-[#9A958D] hover:text-[#6B665E]'">
                        <x-z.icon name="dashboard" class="text-base" /> {{ __('Tableau de bord') }}
                    </button>
                </div>

                <p class="text-xs text-[#9A958D] mb-4 self-start flex items-center gap-1.5">
                    <x-z.icon name="info" class="text-sm" /> {{ __('Exemple fictif du fonctionnement') }}
                </p>

                {{-- Mockup viewport (fixed height so switching doesn't jump) --}}
                <div class="flex items-center justify-center w-full" style="min-height:560px">

                    {{-- WhatsApp phone mockup --}}
                    <div x-show="heroTab === 'whatsapp'" x-transition.opacity class="relative mx-auto" style="width:280px">
                        <div class="relative bg-[#1A1A1A] rounded-[2.8rem] p-3 shadow-2xl shadow-black/30 border-[5px] border-[#1A1A1A]">
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-20 h-5 bg-[#1A1A1A] rounded-b-2xl z-10"></div>
                            <div class="rounded-[2.2rem] overflow-hidden bg-[#ECE5DD]" style="height:480px">
                                {{-- WA header --}}
                                <div class="bg-[#075E54] px-4 py-3 flex items-center gap-3 pt-7">
                                    <div class="w-7 h-7 rounded-full bg-[#C8956C] flex items-center justify-center text-white text-[10px] font-bold shrink-0">ZP</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-xs font-semibold leading-none">Zawaj Pro</p>
                                        <p class="text-green-300 text-[9px] mt-0.5">{{ __('en ligne') }}</p>
                                    </div>
                                    <x-z.icon name="more_vert" class="text-white text-base" />
                                </div>
                                {{-- Chat --}}
                                <div class="p-3 space-y-3 overflow-hidden">
                                    <div class="flex justify-center">
                                        <span class="text-[9px] bg-black/10 text-[#6B665E] px-2 py-0.5 rounded-full">{{ __("Aujourd'hui") }}</span>
                                    </div>
                                    <div class="bg-white rounded-xl rounded-ss-sm p-3 shadow-sm space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <x-z.icon name="notifications" :filled="true" class="text-[#C8956C] text-xs" />
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#C8956C]">{{ __('Nouvelle demande') }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-[#1A1A1A]">📍 {{ __('Alger, El Biar') }}</p>
                                        <div class="grid grid-cols-2 gap-1">
                                            @foreach ([[__('Date'),__('Sept. 2026')],[__('Invités'),__('400 pers.')],[__('Budget'),__('350–500K DA')],[__('Service'),__('Photographe')]] as $kv)
                                                <div class="bg-[#F7F4F0] rounded p-1.5">
                                                    <p class="text-[8px] text-[#9A958D]">{{ $kv[0] }}</p>
                                                    <p class="text-[9px] font-bold text-[#1A1A1A]">{{ $kv[1] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-[9px] text-[#6B665E] leading-relaxed">{{ __('Style naturel. 2 jours de cérémonie.') }}</p>
                                        <p class="text-[8px] text-end text-[#9A958D]">10:14 ✓✓</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-full bg-[#25D366] text-white rounded-xl rounded-ss-sm py-2 px-3 text-[10px] font-bold flex items-center justify-center gap-1.5">
                                            <x-z.icon name="lock_open" :filled="true" class="text-xs" /> {{ trans_choice('provider.leads.btn_accept', $unlockCost, ['count' => $unlockCost]) }}
                                        </div>
                                        <div class="w-full bg-white text-[#6B665E] border border-[#D4CFC7] rounded-xl rounded-ss-sm py-2 px-3 text-[10px] font-semibold flex items-center justify-center gap-1.5">
                                            <x-z.icon name="visibility_off" class="text-xs" /> {{ __('Passer cette demande') }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl rounded-ss-sm p-2.5 shadow-sm">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-1 h-1 rounded-full bg-[#C8956C] animate-pulse"></div>
                                            <p class="text-[9px] text-[#6B665E]">{{ __('3 autres demandes disponibles…') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -inset-6 bg-[#C8956C]/10 rounded-full blur-2xl -z-10 pointer-events-none"></div>
                    </div>

                    {{-- Dashboard mockup --}}
                    <div x-show="heroTab === 'dashboard'" x-transition.opacity x-cloak class="w-full max-w-md">
                        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-xl overflow-hidden">
                            <div class="bg-[#F7F4F0] px-5 py-3.5 border-b border-[#D4CFC7]/40 flex items-center justify-between">
                                <span class="font-serif text-sm font-bold text-[#1A1A1A]">Zawaj Pro</span>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5 px-3 py-1 bg-[#F0D9B5] rounded-full text-xs font-bold text-[#1A1A1A]">
                                        <x-z.icon name="toll" class="text-sm" /> {{ __('250 crédits') }}
                                    </div>
                                    <div class="w-7 h-7 rounded-full bg-[#F0D9B5] flex items-center justify-center text-xs font-bold text-[#1A1A1A]">KA</div>
                                </div>
                            </div>
                            <div class="px-5 py-4 border-b border-[#D4CFC7]/20 grid grid-cols-3 gap-3">
                                @foreach ([['1',__('En priorité')],['4',__('Ouvertes')],['2',__('Acceptées')]] as $stat)
                                    <div class="text-center py-2 bg-[#F7F4F0] rounded-xl">
                                        <p class="font-serif text-xl font-bold text-[#1A1A1A]">{{ $stat[0] }}</p>
                                        <p class="text-[10px] text-[#9A958D] font-medium">{{ $stat[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-4 border-b border-[#D4CFC7]/20">
                                <p class="text-[9px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">{{ __('Demande proposée en priorité') }}</p>
                                <div class="bg-white border-s-4 border-[#A84936] rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-sm text-[#1A1A1A]">{{ __('Alger, El Biar') }}</p>
                                        <p class="text-xs text-[#6B665E]">{{ __('Sept. 2026 · 400 invités · 350–500K DA') }}</p>
                                    </div>
                                    <div class="bg-[#1A1A1A] text-white text-xs px-3 py-1.5 rounded-lg font-bold flex items-center gap-1">
                                        <x-z.icon name="lock" class="text-xs" /> {{ trans_choice('credits.count', $unlockCost, ['count' => $unlockCost]) }}
                                    </div>
                                </div>
                            </div>
                            @foreach ([[__('Alger, Hydra'),__('Août 2026'),__('250 inv.')],[__('Blida, Ouled Yaïch'),__('Juin 2026'),__('180 inv.')],[__('Médéa, Berrouaghia'),__('Sept. 2026'),__('320 inv.')]] as $row)
                                <div class="px-5 py-3 border-b border-[#D4CFC7]/10 flex items-center justify-between hover:bg-[#F7F4F0]/50">
                                    <div>
                                        <p class="text-sm font-semibold text-[#1A1A1A]">{{ $row[0] }}</p>
                                        <p class="text-xs text-[#9A958D]">{{ $row[1] }} · {{ $row[2] }}</p>
                                    </div>
                                    <span class="text-xs text-[#1A1A1A] font-bold flex items-center gap-1">
                                        <x-z.icon name="lock" class="text-xs" /> {{ trans_choice('credits.count', $unlockCost, ['count' => $unlockCost]) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ── Trust bar ─────────────────────────────────────── --}}
    <section class="py-8 bg-white border-y border-[#D4CFC7]/30">
        <div class="max-w-5xl mx-auto px-6 flex flex-wrap justify-center gap-6 md:gap-12">
            @foreach ([[\App\Support\Money::format(0, $currency), __('inscription')], [$unlockCost, $unlockCost === 1 ? __('crédit par demande acceptée') : __('crédits par demande acceptée')], [$openCities->count(), __('wilayas ouvertes actuellement')], [__(':hours h', ['hours' => $roundHours]), __('pour accepter une demande proposée en priorité')]] as $stat)
                <div class="flex items-center gap-3">
                    <span class="font-serif text-2xl font-bold text-[#C8956C]">{{ $stat[0] }}</span>
                    <span class="text-sm text-[#6B665E] font-medium">{{ $stat[1] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── How it works ──────────────────────────────────── --}}
    <section class="py-24 md:py-32 px-5 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Comment ça marche') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('Comment recevoir des demandes') }}</h2>
                <p class="text-[#6B665E] text-lg max-w-lg mx-auto">{{ __('Vous choisissez les demandes auxquelles vous répondez.') }}</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                @foreach ($steps as $i => $s)
                    <div class="relative">
                        @if ($i < count($steps) - 1)
                            <div class="hidden md:block absolute top-10 start-[calc(50%+2rem)] end-[-50%] h-px bg-gradient-to-r from-[#D4CFC7] to-transparent z-0"></div>
                        @endif
                        <div class="relative z-10 bg-white rounded-2xl p-8 border border-[#D4CFC7]/50 shadow-sm hover:shadow-md hover:border-[#C8956C]/40 transition-all duration-300 group h-full">
                            <div class="flex items-center justify-between mb-7">
                                <span class="font-serif text-5xl font-bold text-[#F0D9B5] group-hover:text-[#C8956C]/25 transition-colors">{{ $s['n'] }}</span>
                                <div class="w-12 h-12 rounded-xl bg-[#F7F4F0] group-hover:bg-[#F0D9B5] transition-colors flex items-center justify-center">
                                    <x-z.icon :name="$s['icon']" class="text-[#C8956C] text-2xl" />
                                </div>
                            </div>
                            <h3 class="font-serif text-xl font-bold text-[#1A1A1A] mb-3">{{ $s['title'] }}</h3>
                            <p class="text-[#6B665E] text-sm leading-relaxed">{{ $s['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Why join (bento) ──────────────────────────────── --}}
    <section class="py-24 md:py-32 px-5 md:px-8 bg-[#F7F4F0]">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Pourquoi nous rejoindre') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('Des demandes adaptées à vos services') }}</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="col-span-2 bg-[#1A1A1A] rounded-2xl p-8 md:p-10 flex flex-col justify-between min-h-[220px] relative overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 2px 2px, #F0D9B5 1px, transparent 0);background-size:24px 24px"></div>
                    <div class="relative">
                        <x-z.icon name="visibility" :filled="true" class="text-[#C8956C] text-3xl mb-5" />
                        <h3 class="font-serif text-2xl font-bold text-white mb-2">{{ __('Consultez les détails gratuitement') }}</h3>
                        <p class="text-white/60 text-sm leading-relaxed">{{ __('Service, wilaya, commune, date, budget, invités et description, selon les informations fournies.') }} {{ __("Le prénom et le numéro WhatsApp s'affichent après acceptation.") }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="tune" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Demandes ciblées') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __("Vous recevez les demandes qui correspondent à vos services et à vos wilayas d'intervention.") }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="lock_open" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __("Des crédits utilisés à l'acceptation") }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ $costLine }} {{ $noGuarantee }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="schedule" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Demandes en priorité') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __("Une demande est d'abord proposée à un petit groupe de prestataires (:count au maximum). Si personne n'accepte, elle est ouverte aux prestataires éligibles de la wilaya. Le premier qui accepte obtient les coordonnées.", ['count' => $perRound]) }}</p>
                    </div>
                </div>
                <div class="col-span-2 md:col-span-1 bg-[#F0D9B5]/40 rounded-2xl p-7 border border-[#C8956C]/20 flex flex-col gap-4">
                    <x-z.icon name="support_agent" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __("Besoin d'aide ?") }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __('Une question ? Écrivez-nous depuis votre espace prestataire.') }}</p>
                    </div>
                </div>
                <div class="col-span-2 bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ __('Services proposés') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($services as $s)
                            <span class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7F4F0] rounded-full text-xs font-semibold text-[#6B665E] border border-[#D4CFC7]/50">
                                <x-z.icon :name="$s['icon']" class="text-[#C8956C] text-sm" />{{ $s['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Pricing (real DB packs) ───────────────────────── --}}
    <section id="tarifs" class="py-24 md:py-32 px-5 md:px-8 scroll-mt-28">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Tarifs') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('Choisissez votre pack de crédits') }}</h2>
                <p class="text-[#6B665E] text-lg max-w-md mx-auto">{{ __("Pas d'abonnement. Vous dépensez des crédits uniquement pour les demandes que vous acceptez.") }}</p>
            </div>
            <div class="grid md:grid-cols-3 gap-5">
                @foreach ($packs as $p)
                    @php
                        // Price of one unlocked request: a request costs $unlockCost credits.
                        $perLead = $p->unlocks_count > 0 ? (int) round($p->price * $unlockCost / $p->unlocks_count) : 0;
                    @endphp
                    <div class="relative rounded-2xl p-7 flex flex-col border transition-all {{ $p->is_popular ? 'bg-[#1A1A1A] border-transparent shadow-2xl shadow-black/10 scale-[1.02]' : 'bg-white border-[#D4CFC7]/50 shadow-sm hover:shadow-md' }}">
                        <p class="text-xs font-bold uppercase tracking-widest mb-4 {{ $p->is_popular ? 'text-white/50' : 'text-[#9A958D]' }}">{{ __($p->name) }}</p>
                        <p class="font-serif text-5xl font-bold mb-1 {{ $p->is_popular ? 'text-white' : 'text-[#1A1A1A]' }}">{{ $p->unlocks_count }}</p>
                        <p class="text-sm mb-1 {{ $p->is_popular ? 'text-white/60' : 'text-[#6B665E]' }}">{{ trans_choice('credits.unit', (int) $p->unlocks_count) }} · {{ $unlockCost === 1 ? __('1 crédit = 1 demande') : __(':count crédits = 1 demande', ['count' => $unlockCost]) }}</p>
                        <p class="font-serif text-2xl font-bold mt-4 mb-0.5 {{ $p->is_popular ? 'text-[#C8956C]' : 'text-[#1A1A1A]' }}">{{ \App\Support\Money::format($p->price, $currency) }}</p>
                        <p class="text-xs mb-8 {{ $p->is_popular ? 'text-white/40' : 'text-[#9A958D]' }}">{{ __(':price par demande acceptée', ['price' => \App\Support\Money::format($perLead, $currency)]) }}</p>
                        <x-z.btn :href="route('provider.register')" :variant="$p->is_popular ? 'accent' : 'outline'" class="w-full justify-center mt-auto">
                            {{ $ctaLabel }}
                        </x-z.btn>
                    </div>
                @endforeach
            </div>
            <p class="text-center text-xs text-[#9A958D] mt-8">{{ __("Les crédits n'expirent pas · Reçu de paiement disponible") }}</p>
        </div>
    </section>

    {{-- ── FAQ ───────────────────────────────────────────── --}}
    <section class="py-24 px-5 md:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ __('Questions fréquentes') }}</h2>
            </div>
            <div class="space-y-3">
                @foreach ($faqs as $f)
                    <details class="group rounded-xl border border-[#D4CFC7]/50 bg-white open:border-[#C8956C]/40 hover:border-[#C8956C]/30 transition-all duration-300 overflow-hidden">
                        <summary class="w-full flex items-center justify-between p-5 text-start cursor-pointer list-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C8956C]/40 rounded-xl">
                            <span class="font-semibold text-[#1A1A1A] text-sm md:text-base pe-4">{{ $f['q'] }}</span>
                            <x-z.icon name="add" class="text-xl shrink-0 text-[#9A958D] group-open:hidden" />
                            <x-z.icon name="remove" class="text-xl shrink-0 text-[#C8956C] hidden group-open:inline" />
                        </summary>
                        <div class="px-5 pb-5 text-sm text-[#6B665E] leading-relaxed border-t border-[#D4CFC7]/30 pt-4">
                            {{ $f['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Final CTA ─────────────────────────────────────── --}}
    <section class="px-5 md:px-8 pb-24">
        <div class="max-w-4xl mx-auto bg-[#1A1A1A] rounded-[2rem] px-8 md:px-20 py-16 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image:radial-gradient(circle at 2px 2px, #F0D9B5 1px, transparent 0);background-size:28px 28px"></div>
            <div class="relative">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Rejoignez-nous') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl text-white font-bold mb-5 text-balance">
                    {{ __('Créez votre compte prestataire') }}
                </h2>
                <p class="text-white/60 text-lg mb-10 max-w-lg mx-auto">{{ __('Inscription gratuite. Accès aux demandes après validation de votre profil.') }}</p>
                <x-z.btn :href="route('provider.register')" size="lg" variant="accent">
                    {{ $ctaLabel }} <x-z.icon name="arrow_forward" class="text-lg" />
                </x-z.btn>
            </div>
        </div>
    </section>

</div>
@endsection
