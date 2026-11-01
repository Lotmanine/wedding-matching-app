@extends('layouts.couple')

@section('content')
@php
    // Figures shown on this page come from the real configuration, never from
    // marketing estimates: what we print here is what the platform does.
    $roundHours   = max(1, intdiv((int) config('zawaj.matching.round_timeout_minutes', 120), 60));
    $perRound     = (int) config('zawaj.matching.providers_per_round', 3);
    $maxRounds    = (int) config('zawaj.matching.max_rounds', 3);
    $validityDays = (int) config('zawaj.anpdp.retention_days', 14);
    $isArabic     = app()->getLocale() === 'ar';
    $openWilayas  = \App\Models\Wilaya::active()->pluck($isArabic ? 'name_ar' : 'name_fr');
    $openWilayasText = $isArabic ? $openWilayas->join(' و') : $openWilayas->join(', ', ' et ');

    // ─── Catégories (bento) — adaptées de claude-design/Landing.jsx ───
    // The list follows the active catalog (admin can disable a service): the home
    // page never advertises a service the request form does not offer. Labels come
    // from ServiceType::label(), so marketing, form and provider pages agree.
    $categoryStyles = [
        'photo'      => ['icon' => 'camera_alt',              'gradient' => 'linear-gradient(135deg,#8B6914,#C8956C)', 'image' => asset('images/categories/photographe.jpg'), 'span' => false],
        'video'      => ['icon' => 'videocam',                'gradient' => 'linear-gradient(135deg,#2D3142,#4F5D75)', 'image' => asset('images/categories/videaste.jpg'), 'span' => false],
        'traiteur'   => ['icon' => 'restaurant',              'gradient' => 'linear-gradient(135deg,#6B3A2A,#A0522D)', 'image' => asset('images/categories/traiteur.jpg'), 'span' => false],
        'salle'      => ['icon' => 'villa',                   'gradient' => 'linear-gradient(135deg,#1A2A4A,#2E4070)', 'image' => asset('images/categories/salle.jpg'), 'span' => true],
        'decoration' => ['icon' => 'local_florist',           'gradient' => 'linear-gradient(135deg,#B5707A,#D4919A)', 'image' => asset('images/categories/decoration.jpg'), 'span' => false],
        'patisserie' => ['icon' => 'cake',                    'gradient' => 'linear-gradient(135deg,#C49A82,#E8C8B5)', 'image' => asset('images/categories/patisserie.jpg'), 'span' => false],
        'machta'     => ['icon' => 'face_retouching_natural', 'gradient' => 'linear-gradient(135deg,#5A7A4A,#8AAB6A)', 'image' => asset('images/categories/machta.jpg'), 'span' => false],
        'musique'    => ['icon' => 'music_note',              'gradient' => 'linear-gradient(135deg,#3A2A4A,#6A4F85)', 'image' => null, 'span' => false],
    ];
    $activeServices = collect(\App\Enums\ServiceType::activeCases())->keyBy(fn ($c) => $c->value);
    $categories = [];
    foreach ($categoryStyles as $value => $style) {
        if ($activeServices->has($value)) {
            $categories[] = ['label' => $activeServices[$value]->label()] + $style;
        }
    }
    // Desktop bento: a spanning card takes two cells; pick a column count that leaves no hole when possible.
    $categoryCells = count($categories) + collect($categories)->where('span', true)->count();
    $categoryCols = $categoryCells <= 4 ? max(1, $categoryCells) : ($categoryCells % 4 === 0 ? 4 : ($categoryCells % 3 === 0 ? 3 : 4));
    $categorySpans = $categoryCells > $categoryCols;

    $privacyLink = '<a href="' . e(route('legal.privacy')) . '" class="font-semibold text-[#1A1A1A] underline">' . e(__('form.accept_terms_link')) . '</a>';

    // 'html' => true : the answer is static copy from lang/ holding one escaped link.
    $faqs = [
        ['q' => __("L'envoi de la demande est-il gratuit ?"), 'a' => __("Oui. Les prestataires paient pour accéder à vos coordonnées. Vous convenez du prix des services directement avec eux.")],
        ['q' => __("Comment vérifiez-vous les prestataires ?"), 'a' => __("Notre équipe vérifie chaque prestataire avant l'accès aux demandes. Votre demande est envoyée selon le service recherché et la wilaya.")],
        ['q' => __("Quand un prestataire me contactera-t-il ?"), 'a' => __("Le délai dépend de l'acceptation d'un prestataire. Pour chaque service, nous prévenons jusqu'à :per_round prestataires vérifiés à la fois. Chacun a :hours heures pour répondre. Sans acceptation, nous en prévenons d'autres, :max_rounds tours au maximum. La demande devient ensuite visible par tous les prestataires éligibles de votre wilaya. Elle reste active :days jours : sans acceptation d'ici là, elle expire. Vous suivez l'avancement sur votre page de confirmation.", ['per_round' => $perRound, 'hours' => \App\Support\LocaleCount::hours($roundHours), 'max_rounds' => \App\Support\LocaleCount::rounds($maxRounds), 'days' => \App\Support\LocaleCount::days($validityDays)])],
        ['q' => __("Qui reçoit mon numéro WhatsApp ?"), 'html' => true, 'a' => __("Pour chaque service, seul le prestataire qui accepte votre demande reçoit votre numéro WhatsApp. Votre demande expire :days jours après son envoi. Un prestataire qui l'accepte garde l'accès à vos coordonnées pendant :days jours après son acceptation. Vos données personnelles sont effacées une fois la demande expirée et cette période terminée. Les durées de conservation sont détaillées dans la :link.", ['days' => \App\Support\LocaleCount::days($validityDays), 'link' => $privacyLink])],
        ['q' => __("Et si le prestataire ne me convient pas ?"), 'a' => __("Vous êtes libres de refuser et d'envoyer une nouvelle demande gratuitement.")],
    ];

@endphp

{{-- The layout's <main> applies px/py padding; this landing wants full-bleed sections, so neutralise it. --}}
<div class="-mx-4 sm:-mx-6 -my-8 sm:-my-12 bg-[#FDFAF6]">

    {{-- ── Hero ────────────────────────────────────────────── --}}
    <section class="pt-20 md:pt-28 pb-20 px-5 md:px-8 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(ellipse at 85% 5%, #F0D9B5 0%, transparent 50%)"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-20 items-center relative">
            <div>
                <x-z.badge variant="accent" class="mb-7">
                    <x-z.icon name="auto_awesome" class="text-sm" /> {{ __('Une demande, plusieurs services') }}
                </x-z.badge>
                <h1 class="font-serif text-5xl md:text-6xl lg:text-[72px] text-[#1A1A1A] leading-[1.05] font-bold mb-7 text-balance">
                    {{ __('Trouvez les prestataires de votre mariage') }}
                </h1>
                <p class="text-lg md:text-xl text-[#6B665E] leading-relaxed mb-4 max-w-xl">
                    {{ __("Indiquez vos besoins. Nous envoyons votre demande aux prestataires vérifiés de votre wilaya. Pour chaque service, le premier qui accepte vous contacte sur WhatsApp.") }}
                </p>
                <p class="text-sm text-[#6B665E] mb-10 max-w-xl flex items-start gap-2">
                    <x-z.icon name="verified" :filled="true" class="text-[#C8956C] text-base shrink-0 mt-0.5" />
                    <span>{{ __('Demande gratuite. Prix des services à convenir avec le prestataire.') }}</span>
                </p>
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex flex-col gap-2">
                        <x-z.btn :href="route('couple.request.create')" size="lg" class="shadow-xl shadow-black/10">
                            {{ __('form.nav_cta') }}
                            <x-z.icon name="arrow_forward" class="text-lg rtl:-scale-x-100" />
                        </x-z.btn>
                        <p class="text-xs text-[#9A958D] text-center">{{ __('form.cta_free_note') }}</p>
                    </div>
                    <x-z.btn href="#comment" size="lg" variant="outline">
                        {{ __('Comment ça marche') }}
                    </x-z.btn>
                </div>
            </div>

            {{-- Phone mockup --}}
            <div class="hidden lg:flex justify-center items-center">
                <div class="relative w-[300px] mx-auto">
                    <div class="absolute -inset-8 bg-[#C8956C]/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative w-[280px] bg-[#1A1A1A] rounded-[3rem] p-3 shadow-[0_40px_80px_-20px_rgba(0,0,0,0.35)] border-[6px] border-[#1A1A1A]">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-6 bg-[#1A1A1A] rounded-b-2xl z-10"></div>
                        <div class="w-full rounded-[2.3rem] overflow-hidden bg-[#F0D9B5]/20 flex flex-col" style="height:560px">
                            {{-- WA header --}}
                            <div class="bg-[#1A1A1A] px-4 pt-8 pb-3 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-[#C8956C] flex items-center justify-center text-white text-xs font-bold shrink-0">ZP</div>
                                <div>
                                    <p class="text-white text-sm font-bold leading-none">Zawaj Pro</p>
                                    <p class="text-[#C8956C] text-[10px] mt-0.5">{{ __('En ligne') }}</p>
                                </div>
                            </div>
                            {{-- WA chat --}}
                            <div class="flex-1 bg-[#FDFAF6] p-3 space-y-3 overflow-hidden">
                                <div class="bg-white rounded-2xl rounded-ss-sm p-3 shadow-sm border border-[#D4CFC7]/30 max-w-[85%] text-[11px] leading-relaxed text-[#1A1A1A]">
                                    {{ __('Bonjour, nous cherchons un photographe pour notre mariage à Alger.') }}
                                    <p class="text-[9px] text-end text-[#9A958D] mt-1">10:14</p>
                                </div>
                                <div class="bg-[#F0D9B5] rounded-2xl rounded-se-sm p-3 shadow-sm max-w-[85%] ms-auto text-[11px] leading-relaxed text-[#1A1A1A]">
                                    {!! __("Félicitations 🎉 Nous transmettons votre demande aux <b>photographes vérifiés</b> d'Alger.") !!}
                                    <p class="text-[9px] text-end text-[#7A5530]/60 mt-1">10:14</p>
                                </div>
                                <div class="flex items-center gap-1.5 ms-auto max-w-[85%]">
                                    <div class="bg-[#F0D9B5] rounded-full px-3 py-1.5 text-[10px] font-bold text-[#1A1A1A] flex items-center gap-1">
                                        <x-z.icon name="auto_awesome" :filled="true" class="text-xs" /> {{ __('form.confirm_status_searching') }}
                                    </div>
                                    <div class="flex gap-0.5">
                                        @for ($i = 0; $i < 3; $i++)
                                            <div class="w-1.5 h-1.5 bg-[#C8956C] rounded-full animate-bounce" style="animation-delay:{{ $i * 0.15 }}s"></div>
                                        @endfor
                                    </div>
                                </div>
                                <div class="bg-[#F0D9B5] rounded-2xl rounded-se-sm p-3 shadow-sm max-w-[90%] ms-auto text-[11px] leading-relaxed text-[#1A1A1A]">
                                    {{-- No timestamp here: the example must not suggest a response time. --}}
                                    {!! __('✅ <b>Un photographe</b> a accepté votre demande. Il vous contacte directement sur WhatsApp.') !!}
                                </div>
                            </div>
                            {{-- WA input --}}
                            <div class="bg-white px-3 py-2.5 flex items-center gap-2 border-t border-[#D4CFC7]/30">
                                <div class="flex-1 bg-[#F7F4F0] rounded-full h-8"></div>
                                <div class="w-8 h-8 rounded-full bg-[#1A1A1A] flex items-center justify-center shrink-0">
                                    <x-z.icon name="send" class="text-white text-sm rtl:-scale-x-100" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="relative mt-5 w-[280px] text-xs text-[#9A958D] flex items-center justify-center gap-1.5">
                        <x-z.icon name="info" class="text-sm" /> {{ __('Exemple de fonctionnement') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Trust bar ───────────────────────────────────────── --}}
    <section class="py-8 bg-white border-y border-[#D4CFC7]/30">
        <div class="max-w-5xl mx-auto px-6 flex flex-wrap justify-center gap-6 md:gap-10">
            {{-- The two durations are process facts (provider response window, request validity), not a response promise. --}}
            @foreach ([['0 ' . __('DA'), __('pour les futurs mariés')], [null, __('Disponible à :wilayas', ['wilayas' => $openWilayasText])], [__(':hours h', ['hours' => \App\Support\LocaleCount::hours($roundHours, standalone: true)]), __('Délai de réponse par prestataire')], [__(':days jours', ['days' => \App\Support\LocaleCount::days($validityDays, standalone: true)]), __('Validité de la demande')]] as $stat)
                <div class="flex items-center gap-3">
                    @if ($stat[0] === null)
                        <x-z.icon name="location_on" :filled="true" class="text-2xl text-[#C8956C]" />
                    @else
                        <span class="font-serif text-2xl font-bold text-[#C8956C]">{{ $stat[0] }}</span>
                    @endif
                    <span class="text-sm text-[#6B665E] font-medium">{{ $stat[1] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ── How it works ────────────────────────────────────── --}}
    <section id="comment" class="py-24 md:py-32 px-5 md:px-8 scroll-mt-28">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 md:mb-20">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Comment ça marche') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('Comment envoyer votre demande') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6 md:gap-8 relative">
                <div class="hidden md:block absolute top-10 left-[calc(16.666%+2rem)] right-[calc(16.666%+2rem)] h-px bg-gradient-to-r from-[#D4CFC7] via-[#C8956C] to-[#D4CFC7] z-0"></div>
                @php
                    $steps = [
                        ['n' => '01', 'icon' => 'edit_note', 'title' => __('Décrivez vos besoins'), 'body' => __('Choisissez les services, la wilaya, la date et le budget de votre mariage.')],
                        ['n' => '02', 'icon' => 'send', 'title' => __('Nous envoyons votre demande'), 'body' => __('Elle est transmise aux prestataires vérifiés qui proposent ces services dans votre wilaya.')],
                        ['n' => '03', 'icon' => 'chat_bubble', 'title' => __('Échangez sur WhatsApp'), 'body' => __('Pour chaque service, le premier prestataire qui accepte vous contacte. Vous convenez du prix avec lui.')],
                    ];
                @endphp
                @foreach ($steps as $step)
                    <div class="relative z-10 bg-white rounded-2xl p-8 border border-[#D4CFC7]/50 shadow-sm hover:shadow-md hover:border-[#C8956C]/40 transition-all duration-300 group">
                        <div class="flex items-center justify-between mb-7">
                            <span class="font-serif text-5xl font-bold text-[#F0D9B5] group-hover:text-[#C8956C]/30 transition-colors">{{ $step['n'] }}</span>
                            <div class="w-12 h-12 rounded-xl bg-[#F7F4F0] group-hover:bg-[#F0D9B5] transition-colors flex items-center justify-center">
                                <x-z.icon :name="$step['icon']" class="text-[#C8956C] text-2xl" />
                            </div>
                        </div>
                        <h3 class="font-serif text-xl font-bold text-[#1A1A1A] mb-3">{{ $step['title'] }}</h3>
                        <p class="text-[#6B665E] leading-relaxed text-sm">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Comparison ──────────────────────────────────────── --}}
    <section class="py-24 md:py-32 px-5 md:px-8 bg-[#F7F4F0]">
        <div class="max-w-4xl mx-auto">
            <h2 class="font-serif text-4xl md:text-5xl font-bold text-center text-[#1A1A1A] mb-14">{{ __('Pourquoi Zawaj Pro ?') }}</h2>
            <div class="grid md:grid-cols-2 rounded-2xl overflow-hidden shadow-xl shadow-black/5 border border-[#D4CFC7]/40">
                {{-- Without --}}
                <div class="bg-white p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500 font-bold text-sm">×</div>
                        <h3 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ __('Recherche classique') }}</h3>
                    </div>
                    <ul class="space-y-5">
                        @foreach ([__('Chercher dans plusieurs groupes'), __('Vérifier chaque prestataire vous-même'), __('Contacter chaque prestataire un par un'), __('Attendre les réponses séparément')] as $t)
                            <li class="flex items-start gap-3 text-sm text-[#6B665E]">
                                <x-z.icon name="remove_circle_outline" class="text-[#D4CFC7] text-lg shrink-0 mt-0.5" />
                                {{ $t }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                {{-- With --}}
                <div class="bg-white p-8 md:p-10 relative">
                    <div class="absolute inset-y-0 start-0 w-1 bg-gradient-to-b from-[#F0D9B5] via-[#C8956C] to-[#F0D9B5]"></div>
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-8 h-8 rounded-full bg-[#F0D9B5] flex items-center justify-center">
                            <x-z.icon name="check" :filled="true" class="text-[#C8956C] text-sm" />
                        </div>
                        <h3 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ __('Avec Zawaj Pro') }}</h3>
                    </div>
                    <ul class="space-y-5">
                        @foreach ([__('Une seule demande, transmise aux prestataires de votre wilaya'), __('Chaque prestataire est vérifié par notre équipe'), __('Chaque prestataire sollicité a :hours heures pour répondre', ['hours' => \App\Support\LocaleCount::hours($roundHours)]), __('Contact direct sur WhatsApp avec le prestataire qui accepte')] as $t)
                            <li class="flex items-start gap-3 text-sm font-semibold text-[#1A1A1A]">
                                <x-z.icon name="check_circle" :filled="true" class="text-[#C8956C] text-lg shrink-0 mt-0.5" />
                                {{ $t }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Categories ──────────────────────────────────────── --}}
    <section class="py-24 md:py-32 px-5 md:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 mb-12">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Nos catégories') }}</p>
                    <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('Quels services recherchez-vous ?') }}</h2>
                </div>
                <p class="text-[#6B665E] max-w-xs text-sm leading-relaxed">{{ __('Choisissez un ou plusieurs services.') }}</p>
            </div>

            {{-- Reusable card markup: render once per layout (desktop bento + mobile grid) --}}
            @php
                $renderCategory = function ($c, $span) {
                    $style = 'background:' . $c['gradient'] . ($span && $c['span'] ? ';grid-row:span 2' : '');
                    ob_start(); ?>
                    <div class="group relative rounded-2xl overflow-hidden cursor-pointer" style="<?php echo $style; ?>">
                        <?php if ($c['image']) { ?>
                        <img src="<?php echo $c['image']; ?>" alt="<?php echo e($c['label']); ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy">
                        <?php } ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-[#1A1A1A]/85 via-[#1A1A1A]/15 to-transparent group-hover:from-[#1A1A1A]/90 transition-all duration-500"></div>
                        <div class="absolute top-4 end-4 w-9 h-9 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <span class="material-symbols-outlined text-white text-lg" aria-hidden="true"><?php echo $c['icon']; ?></span>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-5 md:p-6">
                            <p class="font-serif text-white text-xl font-bold mb-1 group-hover:-translate-y-0.5 transition-transform duration-300"><?php echo e($c['label']); ?></p>
                        </div>
                    </div>
                    <?php return ob_get_clean();
                };
            @endphp

            {{-- Desktop bento — Salle des fêtes spans 2 rows when it is offered and the grid has more than one row --}}
            <div class="hidden lg:grid gap-4" style="grid-template-columns:repeat({{ $categoryCols }},1fr);grid-auto-rows:300px">
                @foreach ($categories as $c)
                    {!! $renderCategory($c, $categorySpans) !!}
                @endforeach
            </div>
            {{-- Mobile 2-col --}}
            <div class="grid grid-cols-2 gap-3 lg:hidden">
                @foreach ($categories as $c)
                    <div class="h-52">{!! $renderCategory($c, false) !!}</div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Mission / humanizing ────────────────────────────── --}}
    <section class="py-24 md:py-32 px-5 md:px-8 bg-[#F7F4F0]">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            {{-- Image collage --}}
            <div class="relative h-[420px] md:h-[520px]">
                <div class="absolute top-0 start-0 w-[58%] h-[65%] rounded-2xl overflow-hidden shadow-xl shadow-black/10">
                    <img src="{{ asset('images/gallery/mariage.jpg') }}" alt="{{ __('Mariage algérien') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute bottom-0 start-[8%] w-[48%] h-[48%] rounded-2xl overflow-hidden shadow-xl shadow-black/10 border-4 border-white">
                    <img src="{{ asset('images/gallery/couple.jpg') }}" alt="{{ __('Couple heureux') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute top-[10%] end-0 w-[38%] h-[55%] rounded-2xl overflow-hidden shadow-xl shadow-black/10">
                    <img src="{{ asset('images/gallery/ceremonie.jpg') }}" alt="{{ __('Cérémonie') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute bottom-[15%] end-[2%] bg-white rounded-2xl p-4 shadow-lg shadow-black/10 border border-[#D4CFC7]/40">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F0D9B5] flex items-center justify-center shrink-0">
                            <x-z.icon name="favorite" :filled="true" class="text-[#C8956C] text-xl" />
                        </div>
                        <div>
                            <p class="font-serif text-2xl font-bold text-[#1A1A1A] leading-none">0 {{ __('DA') }}</p>
                            <p class="text-xs text-[#9A958D] mt-0.5">{{ __('pour les futurs mariés') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Text --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Notre mission') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] leading-[1.1] mb-6 text-balance">
                    {{ __('Des prestataires vérifiés dans votre wilaya') }}
                </h2>
                <p class="text-[#6B665E] leading-relaxed text-lg mb-8">
                    {{ __("Notre équipe vérifie les prestataires avant l'accès aux demandes. Vous échangez directement avec eux et convenez du prix.") }}
                </p>
                <div class="space-y-5">
                    @php
                        $missionItems = [
                            // The verification card was dropped: the heading and paragraph above already say it.
                            ['icon' => 'shield', 'title' => __('Qui voit votre numéro ?'), 'body' => __('Pour chaque service, seul le prestataire qui accepte votre demande reçoit votre numéro WhatsApp.'), 'email' => null],
                            ['icon' => 'support_agent', 'title' => __("Besoin d'aide ?"), 'body' => __('Une question ? Écrivez-nous.'), 'email' => 'contact@zawajpro.dz'],
                        ];
                    @endphp
                    @foreach ($missionItems as $item)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-[#D4CFC7]/50 flex items-center justify-center shrink-0 shadow-sm">
                                <x-z.icon :name="$item['icon']" :filled="true" class="text-[#C8956C] text-xl" />
                            </div>
                            <div>
                                <p class="font-semibold text-[#1A1A1A] mb-0.5">{{ $item['title'] }}</p>
                                <p class="text-sm text-[#6B665E] leading-relaxed">{{ $item['body'] }}
                                    @if ($item['email'])
                                        <a href="mailto:{{ $item['email'] }}" dir="ltr" class="font-semibold text-[#1A1A1A] underline">{{ $item['email'] }}</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ── FAQ ─────────────────────────────────────────────── --}}
    <section id="faq" class="py-24 md:py-32 px-5 md:px-8 scroll-mt-28">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ __('Questions fréquentes') }}</h2>
            </div>
            <div class="space-y-3">
                @foreach ($faqs as $f)
                    <details class="group rounded-xl border border-[#D4CFC7]/50 bg-white open:border-[#C8956C]/40 hover:border-[#C8956C]/30 transition-all duration-300 overflow-hidden">
                        <summary class="w-full flex items-center justify-between p-5 md:p-6 text-start cursor-pointer list-none focus:outline-none focus-visible:ring-2 focus-visible:ring-[#C8956C]/40 rounded-xl">
                            <span class="font-semibold text-[#1A1A1A] text-sm md:text-base pe-4">{{ $f['q'] }}</span>
                            <x-z.icon name="add" class="text-xl shrink-0 text-[#9A958D] group-open:hidden" />
                            <x-z.icon name="remove" class="text-xl shrink-0 text-[#C8956C] hidden group-open:inline" />
                        </summary>
                        <div class="px-5 md:px-6 pb-5 text-sm text-[#6B665E] leading-relaxed border-t border-[#D4CFC7]/30 pt-4">
                            @if (! empty($f['html'])) {!! $f['a'] !!} @else {{ $f['a'] }} @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── Final CTA ───────────────────────────────────────── --}}
    <section class="px-5 md:px-8 pb-24">
        <div class="max-w-5xl mx-auto">
            <div class="bg-[#1A1A1A] rounded-[2rem] px-8 md:px-20 py-16 md:py-20 text-center relative overflow-hidden">
                <div class="absolute inset-0 pointer-events-none opacity-[0.04]" style="background-image:radial-gradient(circle at 2px 2px, #F0D9B5 1px, transparent 0);background-size:28px 28px"></div>
                <div class="relative">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Commencez maintenant') }}</p>
                    <h2 class="font-serif text-4xl md:text-5xl text-white font-bold mb-6 leading-tight text-balance">
                        {{ __('Commençons par votre mariage') }}
                    </h2>
                    <p class="text-white/60 text-lg mb-10 max-w-xl mx-auto">
                        {{ __('Demande gratuite, sans engagement.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-center gap-4">
                        <div class="flex flex-col gap-2">
                            <x-z.btn :href="route('couple.request.create')" size="lg" variant="accent">
                                {{ __('form.nav_cta') }} <x-z.icon name="arrow_forward" class="text-lg rtl:-scale-x-100" />
                            </x-z.btn>
                            <p class="text-xs text-white/50 text-center">{{ __('form.cta_free_note') }}</p>
                        </div>
                        <x-z.btn :href="route('provider.register')" size="lg" variant="ghost-white">
                            {{ __('Créer un compte prestataire') }}
                        </x-z.btn>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
