@extends('layouts.couple')

@section('description', __('Portfolio demo of a wedding-services marketplace built with Laravel and Livewire. Couples send one request and local providers are matched through timed rounds.'))

{{-- Structured data: the site and the developer who built it. The array is
     built in a PHP block: inside echo tags, Blade would compile the "context"
     key as its own directive. --}}
@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => __('form.brand'),
        'url' => route('home'),
        'description' => __('Portfolio demo of a wedding-services marketplace built with Laravel and Livewire. Couples send one request and local providers are matched through timed rounds.'),
        'inLanguage' => app()->getLocale(),
        'creator' => [
            '@type' => 'Person',
            'name' => 'Lotfi Otmanine',
            'url' => 'https://oldev.site',
            'sameAs' => [
                'https://github.com/Lotmanine',
                'https://www.linkedin.com/in/lotfi-otmanine',
                'https://www.upwork.com/freelancers/~01bd748fcb665b90ca',
            ],
        ],
    ];
@endphp
@push('head')
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
@php
    // Figures shown on this page come from the real configuration, never from
    // marketing estimates: what we print here is what the platform does.
    $roundHours   = max(1, intdiv((int) config('wedding.matching.round_timeout_minutes', 120), 60));
    $perRound     = (int) config('wedding.matching.providers_per_round', 3);
    $maxRounds    = (int) config('wedding.matching.max_rounds', 3);
    $validityDays = (int) config('wedding.privacy.retention_days', 14);
    $openCities   = \App\Models\City::active()->pluck('name');
    $openCitiesText = $openCities->join(', ', ' ' . __('and') . ' ');
    $freeLabel    = \App\Support\Money::format(0, \App\Support\Money::currencyForCity(null));

    // ─── Catégories (bento) — adaptées de claude-design/Landing.jsx ───
    // The list follows the active catalog (admin can disable a service): the home
    // page never advertises a service the request form does not offer. Labels come
    // from ServiceType::label(), so marketing, form and provider pages agree.
    $categoryStyles = [
        'photo'      => ['icon' => 'camera_alt',              'gradient' => 'linear-gradient(135deg,#8B6914,#C8956C)', 'image' => asset('images/categories/photo.jpg'), 'span' => false],
        'video'      => ['icon' => 'videocam',                'gradient' => 'linear-gradient(135deg,#2D3142,#4F5D75)', 'image' => asset('images/categories/video.jpg'), 'span' => false],
        'catering'   => ['icon' => 'restaurant',              'gradient' => 'linear-gradient(135deg,#6B3A2A,#A0522D)', 'image' => asset('images/categories/catering.jpg'), 'span' => false],
        'venue'      => ['icon' => 'villa',                   'gradient' => 'linear-gradient(135deg,#1A2A4A,#2E4070)', 'image' => asset('images/categories/venue.jpg'), 'span' => true],
        'decoration' => ['icon' => 'local_florist',           'gradient' => 'linear-gradient(135deg,#B5707A,#D4919A)', 'image' => asset('images/categories/decoration.jpg'), 'span' => false],
        'pastry' => ['icon' => 'cake',                    'gradient' => 'linear-gradient(135deg,#C49A82,#E8C8B5)', 'image' => asset('images/categories/pastry.jpg'), 'span' => false],
        'beauty'     => ['icon' => 'face_retouching_natural', 'gradient' => 'linear-gradient(135deg,#5A7A4A,#8AAB6A)', 'image' => asset('images/categories/beauty.jpg'), 'span' => false],
        'music'    => ['icon' => 'music_note',              'gradient' => 'linear-gradient(135deg,#3A2A4A,#6A4F85)', 'image' => null, 'span' => false],
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
        ['q' => __('Is sending a request free?'), 'a' => __('Yes. Providers pay to access your contact details. You agree on the price of services directly with them.')],
        ['q' => __('How are providers verified?'), 'a' => __('An admin verifies each provider before they can see requests. Your request is sent according to the service you need and your city.')],
        ['q' => __('When will a provider contact me?'), 'a' => __('It depends on when a provider accepts. For each service, we notify up to :per_round verified providers at a time. Each has :hours hours to respond. If nobody accepts, we notify others, for up to :max_rounds rounds. The request then becomes visible to all eligible providers in your city. It stays active for :days days: if nobody accepts by then, it expires. You can follow the progress on your confirmation page.', ['per_round' => $perRound, 'hours' => $roundHours, 'max_rounds' => $maxRounds, 'days' => $validityDays])],
        ['q' => __('Who receives my WhatsApp number?'), 'html' => true, 'a' => __('For each service, only the provider who accepts your request receives your WhatsApp number. Your request expires :days days after it is sent. A provider who accepts it keeps access to your contact details for :days days after accepting. Your personal details are erased once the request has expired and that period is over. Retention periods are explained in the :link.', ['days' => $validityDays, 'link' => $privacyLink])],
        ['q' => __('What if the provider is not right for me?'), 'a' => __('You are free to say no and send a new request at no cost.')],
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
                    <x-z.icon name="auto_awesome" class="text-sm" /> {{ __('One request, several services') }}
                </x-z.badge>
                <h1 class="font-serif text-5xl md:text-6xl lg:text-[72px] text-[#1A1A1A] leading-[1.05] font-bold mb-7 text-balance">
                    {{ __('Find the providers for your wedding') }}
                </h1>
                <p class="text-lg md:text-xl text-[#6B665E] leading-relaxed mb-4 max-w-xl">
                    {{ __('Tell us what you need. We send your request to verified providers in your city. For each service, the first to accept contacts you on WhatsApp.') }}
                </p>
                <p class="text-sm text-[#6B665E] mb-10 max-w-xl flex items-start gap-2">
                    <x-z.icon name="verified" :filled="true" class="text-[#C8956C] text-base shrink-0 mt-0.5" />
                    <span>{{ __('Free request. Prices for services are agreed with the provider.') }}</span>
                </p>
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex flex-col gap-2">
                        <x-z.btn :href="route('couple.request.create')" size="lg" class="shadow-xl shadow-black/10">
                            {{ __('form.nav_cta') }}
                            <x-z.icon name="arrow_forward" class="text-lg" />
                        </x-z.btn>
                        <p class="text-xs text-[#9A958D] text-center">{{ __('form.cta_free_note') }}</p>
                    </div>
                    <x-z.btn href="#how-it-works" size="lg" variant="outline">
                        {{ __('How it works') }}
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
                                <div class="w-9 h-9 rounded-full bg-[#C8956C] flex items-center justify-center text-white text-xs font-bold shrink-0">WM</div>
                                <div>
                                    <p class="text-white text-sm font-bold leading-none">{{ __('form.brand') }}</p>
                                    <p class="text-[#C8956C] text-[10px] mt-0.5">{{ __('Online') }}</p>
                                </div>
                            </div>
                            {{-- WA chat --}}
                            <div class="flex-1 bg-[#FDFAF6] p-3 space-y-3 overflow-hidden">
                                <div class="bg-white rounded-2xl rounded-ss-sm p-3 shadow-sm border border-[#D4CFC7]/30 max-w-[85%] text-[11px] leading-relaxed text-[#1A1A1A]">
                                    {{ __('Hello, we are looking for a photographer for our wedding in New York.') }}
                                    <p class="text-[9px] text-end text-[#9A958D] mt-1">10:14</p>
                                </div>
                                <div class="bg-[#F0D9B5] rounded-2xl rounded-se-sm p-3 shadow-sm max-w-[85%] ms-auto text-[11px] leading-relaxed text-[#1A1A1A]">
                                    {!! __('Congratulations 🎉 We are sending your request to <b>verified photographers</b> in New York.') !!}
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
                                    {!! __('✅ <b>A photographer</b> has accepted your request. They will contact you directly on WhatsApp.') !!}
                                </div>
                            </div>
                            {{-- WA input --}}
                            <div class="bg-white px-3 py-2.5 flex items-center gap-2 border-t border-[#D4CFC7]/30">
                                <div class="flex-1 bg-[#F7F4F0] rounded-full h-8"></div>
                                <div class="w-8 h-8 rounded-full bg-[#1A1A1A] flex items-center justify-center shrink-0">
                                    <x-z.icon name="send" class="text-white text-sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="relative mt-5 w-[280px] text-xs text-[#9A958D] flex items-center justify-center gap-1.5">
                        <x-z.icon name="info" class="text-sm" /> {{ __('Example of how it works') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Trust bar ───────────────────────────────────────── --}}
    <section class="py-8 bg-white border-y border-[#D4CFC7]/30">
        <div class="max-w-5xl mx-auto px-6 flex flex-wrap justify-center gap-6 md:gap-10">
            {{-- The two durations are process facts (provider response window, request validity), not a response promise. --}}
            @foreach ([[$freeLabel, __('for couples')], [null, __('Available in :cities', ['cities' => $openCitiesText])], [__(':hours h', ['hours' => $roundHours]), __('Response window per provider')], [trans_choice('form.days_count', $validityDays, ['count' => $validityDays]), __('Request validity')]] as $stat)
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
    <section id="how-it-works" class="py-24 md:py-32 px-5 md:px-8 scroll-mt-28">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 md:mb-20">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('How it works') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('How to send your request') }}</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6 md:gap-8 relative">
                <div class="hidden md:block absolute top-10 left-[calc(16.666%+2rem)] right-[calc(16.666%+2rem)] h-px bg-gradient-to-r from-[#D4CFC7] via-[#C8956C] to-[#D4CFC7] z-0"></div>
                @php
                    $steps = [
                        ['n' => '01', 'icon' => 'edit_note', 'title' => __('Describe your needs'), 'body' => __('Choose the services, city, date and budget for your wedding.')],
                        ['n' => '02', 'icon' => 'send', 'title' => __('We send your request'), 'body' => __('It goes to verified providers offering these services in your city.')],
                        ['n' => '03', 'icon' => 'chat_bubble', 'title' => __('Talk on WhatsApp'), 'body' => __('For each service, the first provider to accept contacts you. You agree on the price together.')],
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
            <h2 class="font-serif text-4xl md:text-5xl font-bold text-center text-[#1A1A1A] mb-14">{{ __('Why :brand?', ['brand' => __('form.brand')]) }}</h2>
            <div class="grid md:grid-cols-2 rounded-2xl overflow-hidden shadow-xl shadow-black/5 border border-[#D4CFC7]/40">
                {{-- Without --}}
                <div class="bg-white p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-500 font-bold text-sm">×</div>
                        <h3 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ __('Searching on your own') }}</h3>
                    </div>
                    <ul class="space-y-5">
                        @foreach ([__('Look through many listings'), __('Check each provider yourself'), __('Contact each provider one by one'), __('Wait for replies separately')] as $t)
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
                        <h3 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ __('With :brand', ['brand' => __('form.brand')]) }}</h3>
                    </div>
                    <ul class="space-y-5">
                        @foreach ([__('One request, sent to providers in your city'), __('Every provider is verified by an admin'), __('Each provider contacted has :hours hours to respond', ['hours' => $roundHours]), __('Direct contact on WhatsApp with the provider who accepts')] as $t)
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
                    <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Our categories') }}</p>
                    <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('Which services are you looking for?') }}</h2>
                </div>
                <p class="text-[#6B665E] max-w-xs text-sm leading-relaxed">{{ __('Choose one or more services.') }}</p>
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

            {{-- Desktop bento — the venue card spans 2 rows when it is offered and the grid has more than one row --}}
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
                    <img src="{{ asset('images/gallery/wedding.jpg') }}" alt="{{ __('Wedding celebration') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute bottom-0 start-[8%] w-[48%] h-[48%] rounded-2xl overflow-hidden shadow-xl shadow-black/10 border-4 border-white">
                    <img src="{{ asset('images/gallery/couple.jpg') }}" alt="{{ __('Happy couple') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute top-[10%] end-0 w-[38%] h-[55%] rounded-2xl overflow-hidden shadow-xl shadow-black/10">
                    <img src="{{ asset('images/gallery/ceremony.jpg') }}" alt="{{ __('Ceremony') }}" class="w-full h-full object-cover" loading="lazy">
                </div>
                <div class="absolute bottom-[15%] end-[2%] bg-white rounded-2xl p-4 shadow-lg shadow-black/10 border border-[#D4CFC7]/40">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#F0D9B5] flex items-center justify-center shrink-0">
                            <x-z.icon name="favorite" :filled="true" class="text-[#C8956C] text-xl" />
                        </div>
                        <div>
                            <p class="font-serif text-2xl font-bold text-[#1A1A1A] leading-none">{{ $freeLabel }}</p>
                            <p class="text-xs text-[#9A958D] mt-0.5">{{ __('for couples') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Text --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Who you deal with') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] leading-[1.1] mb-6 text-balance">
                    {{ __('Verified providers in your city') }}
                </h2>
                <p class="text-[#6B665E] leading-relaxed text-lg mb-8">
                    {{ __('An admin verifies providers before they can see requests. You talk to them directly and agree on the price.') }}
                </p>
                <div class="space-y-5">
                    @php
                        $missionItems = [
                            // The verification card was dropped: the heading and paragraph above already say it.
                            ['icon' => 'shield', 'title' => __('Who sees your number?'), 'body' => __('For each service, only the provider who accepts your request receives your WhatsApp number.')],
                            ['icon' => 'support_agent', 'title' => __('Need help?'), 'body' => __('This is a demo, so there is no support service.')],
                        ];
                    @endphp
                    @foreach ($missionItems as $item)
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white border border-[#D4CFC7]/50 flex items-center justify-center shrink-0 shadow-sm">
                                <x-z.icon :name="$item['icon']" :filled="true" class="text-[#C8956C] text-xl" />
                            </div>
                            <div>
                                <p class="font-semibold text-[#1A1A1A] mb-0.5">{{ $item['title'] }}</p>
                                <p class="text-sm text-[#6B665E] leading-relaxed">{{ $item['body'] }}</p>
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
                <h2 class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ __('Frequently asked questions') }}</h2>
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
                    <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Get started') }}</p>
                    <h2 class="font-serif text-4xl md:text-5xl text-white font-bold mb-6 leading-tight text-balance">
                        {{ __("Let's start with your wedding") }}
                    </h2>
                    <p class="text-white/60 text-lg mb-10 max-w-xl mx-auto">
                        {{ __('Free request, no commitment.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row sm:items-start justify-center gap-4">
                        <div class="flex flex-col gap-2">
                            <x-z.btn :href="route('couple.request.create')" size="lg" variant="accent">
                                {{ __('form.nav_cta') }} <x-z.icon name="arrow_forward" class="text-lg" />
                            </x-z.btn>
                            <p class="text-xs text-white/50 text-center">{{ __('form.cta_free_note') }}</p>
                        </div>
                        <x-z.btn :href="route('provider.register')" size="lg" variant="ghost-white">
                            {{ __('Create a provider account') }}
                        </x-z.btn>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
