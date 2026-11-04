{{--
    ═══════════════════════════════════════════════════════
    Provider landing page. Sells the platform to providers;
    the buttons lead to the registration form. Pricing reads
    the active UnlockPack rows.
    ═══════════════════════════════════════════════════════
--}}
@extends('layouts.couple')

@section('title', __('For providers · :brand', ['brand' => __('form.brand')]))

@section('description', __("How wedding providers receive matched requests and unlock a couple's contact details with credits, in this portfolio demo marketplace."))

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
    $openCitiesText = $openCities->join(', ', ' ' . __('and') . ' ');
    $currency     = \App\Support\Money::currencyForCity(null);
    $mockBudget   = \App\Enums\BudgetRange::From35kTo50k->label($currency);

    // Buttons that open registration: /provider/register is guest-only, so a
    // signed-in visitor is redirected to their own space. Label accordingly.
    $isSignedIn = auth()->check();
    $ctaLabel   = $isSignedIn ? __('Dashboard') : __('Create my account');

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

    $noGuarantee = __('Accepting guarantees neither a reply nor a booking.');
    $costLine    = trans_choice('credits.unlock_cost_line', $unlockCost, ['count' => $unlockCost]);

    $steps = [
        ['n' => '01', 'icon' => 'inbox',      'title' => __('Receive requests'), 'body' => __('They match your services and the cities you work in. Check that you are free on the wedding date.')],
        ['n' => '02', 'icon' => 'visibility', 'title' => __('Choose a request'), 'body' => __('View the service, location, date, budget and available details for free.')],
        ['n' => '03', 'icon' => 'chat',       'title' => __('Contact the couple'), 'body' => __('Accept at the displayed cost to see the first name and WhatsApp number. Agree on the price directly with the couple.') . ' ' . $noGuarantee],
    ];

    $faqs = [
        ['q' => __('How do I receive requests?'), 'a' => __('Requests matching your services and the cities you work in appear in your dashboard. You can view their details for free. The platform does not know your calendar: check the wedding date before accepting.')],
        ['q' => __('What are credits for?'), 'a' => __('Credits are sold in packs. Accepting a request spends the displayed cost and gives you the first name and WhatsApp number. It guarantees neither a reply nor a booking.') . ' ' . $costLine],
        ['q' => __('Can I decline a request?'), 'a' => __('Yes. You choose which requests to accept. Declining costs no credits.')],
        ['q' => __('How is my profile verified?'), 'a' => __('After you sign up, an admin reviews your profile before giving you access to requests. Until your profile is approved, you do not receive requests.')],
        ['q' => __('Is there a subscription?'), 'a' => __('No. You buy credits as you need them, with no recurring fees.')],
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
                    <x-z.icon name="storefront" class="text-sm" /> {{ __('Available in :cities', ['cities' => $openCitiesText]) }}
                </x-z.badge>
                <h1 class="font-serif text-5xl md:text-6xl lg:text-[68px] text-[#1A1A1A] font-bold leading-[1.05] mb-6 text-balance">
                    {{ __('Receive wedding requests') }}
                </h1>
                <p class="text-lg md:text-xl text-[#6B665E] leading-relaxed mb-10 max-w-xl">
                    {{ __('Requests for your services, in the cities where you work. View the details for free and choose which ones to accept.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <x-z.btn :href="route('provider.register')" size="lg" variant="accent" class="shadow-xl shadow-[#C8956C]/20">
                        {{ $ctaLabel }} <x-z.icon name="arrow_forward" class="text-lg" />
                    </x-z.btn>
                    @guest
                        <x-z.btn :href="route('provider.login')" size="lg" variant="outline">
                            {{ __('Log in') }}
                        </x-z.btn>
                    @endguest
                </div>
                <p class="text-xs text-[#9A958D] mt-5 flex items-center gap-2">
                    <x-z.icon name="verified" :filled="true" class="text-[#C8956C] text-base" /> {{ __('Free sign-up · Profile reviewed by an admin · No subscription') }}
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
                        <x-z.icon name="dashboard" class="text-base" /> {{ __('Dashboard') }}
                    </button>
                </div>

                <p class="text-xs text-[#9A958D] mb-4 self-start flex items-center gap-1.5">
                    <x-z.icon name="info" class="text-sm" /> {{ __('Fictional example of how it works') }}
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
                                    <div class="w-7 h-7 rounded-full bg-[#C8956C] flex items-center justify-center text-white text-[10px] font-bold shrink-0">WM</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-white text-xs font-semibold leading-none">{{ __('form.brand') }}</p>
                                        <p class="text-green-300 text-[9px] mt-0.5">{{ __('online') }}</p>
                                    </div>
                                    <x-z.icon name="more_vert" class="text-white text-base" />
                                </div>
                                {{-- Chat --}}
                                <div class="p-3 space-y-3 overflow-hidden">
                                    <div class="flex justify-center">
                                        <span class="text-[9px] bg-black/10 text-[#6B665E] px-2 py-0.5 rounded-full">{{ __('Today') }}</span>
                                    </div>
                                    <div class="bg-white rounded-xl rounded-ss-sm p-3 shadow-sm space-y-2">
                                        <div class="flex items-center gap-1.5">
                                            <x-z.icon name="notifications" :filled="true" class="text-[#C8956C] text-xs" />
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-[#C8956C]">{{ __('New request') }}</span>
                                        </div>
                                        <p class="text-xs font-bold text-[#1A1A1A]">📍 {{ __('New York, Brooklyn') }}</p>
                                        <div class="grid grid-cols-2 gap-1">
                                            @foreach ([[__('Date'),__('Sep. 2027')],[__('Guests'),__('150 guests')],[__('Budget'),$mockBudget],[__('Service'),__('Photographer')]] as $kv)
                                                <div class="bg-[#F7F4F0] rounded p-1.5">
                                                    <p class="text-[8px] text-[#9A958D]">{{ $kv[0] }}</p>
                                                    <p class="text-[9px] font-bold text-[#1A1A1A]">{{ $kv[1] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="text-[9px] text-[#6B665E] leading-relaxed">{{ __('Natural style. Two-day celebration.') }}</p>
                                        <p class="text-[8px] text-end text-[#9A958D]">10:14 ✓✓</p>
                                    </div>
                                    <div class="space-y-1.5">
                                        <div class="w-full bg-[#25D366] text-white rounded-xl rounded-ss-sm py-2 px-3 text-[10px] font-bold flex items-center justify-center gap-1.5">
                                            <x-z.icon name="lock_open" :filled="true" class="text-xs" /> {{ trans_choice('provider.leads.btn_accept', $unlockCost, ['count' => $unlockCost]) }}
                                        </div>
                                        <div class="w-full bg-white text-[#6B665E] border border-[#D4CFC7] rounded-xl rounded-ss-sm py-2 px-3 text-[10px] font-semibold flex items-center justify-center gap-1.5">
                                            <x-z.icon name="visibility_off" class="text-xs" /> {{ __('Skip this request') }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-xl rounded-ss-sm p-2.5 shadow-sm">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-1 h-1 rounded-full bg-[#C8956C] animate-pulse"></div>
                                            <p class="text-[9px] text-[#6B665E]">{{ __('3 more requests available…') }}</p>
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
                                <span class="font-serif text-sm font-bold text-[#1A1A1A]">{{ __('form.brand') }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5 px-3 py-1 bg-[#F0D9B5] rounded-full text-xs font-bold text-[#1A1A1A]">
                                        <x-z.icon name="toll" class="text-sm" /> {{ __('250 credits') }}
                                    </div>
                                    <div class="w-7 h-7 rounded-full bg-[#F0D9B5] flex items-center justify-center text-xs font-bold text-[#1A1A1A]">EM</div>
                                </div>
                            </div>
                            <div class="px-5 py-4 border-b border-[#D4CFC7]/20 grid grid-cols-3 gap-3">
                                @foreach ([['1',__('Priority')],['4',__('Open')],['2',__('Accepted')]] as $stat)
                                    <div class="text-center py-2 bg-[#F7F4F0] rounded-xl">
                                        <p class="font-serif text-xl font-bold text-[#1A1A1A]">{{ $stat[0] }}</p>
                                        <p class="text-[10px] text-[#9A958D] font-medium">{{ $stat[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-4 border-b border-[#D4CFC7]/20">
                                <p class="text-[9px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">{{ __('Priority request') }}</p>
                                <div class="bg-white border-s-4 border-[#A84936] rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <p class="font-bold text-sm text-[#1A1A1A]">{{ __('New York, Brooklyn') }}</p>
                                        <p class="text-xs text-[#6B665E]">{{ __('Sep. 2027 · 150 guests') }} · {{ $mockBudget }}</p>
                                    </div>
                                    <div class="bg-[#1A1A1A] text-white text-xs px-3 py-1.5 rounded-lg font-bold flex items-center gap-1">
                                        <x-z.icon name="lock" class="text-xs" /> {{ trans_choice('credits.count', $unlockCost, ['count' => $unlockCost]) }}
                                    </div>
                                </div>
                            </div>
                            @foreach ([[__('Chicago, Lincoln Park'),__('Aug. 2027'),__('120 guests')],[__('Miami, Wynwood'),__('Jun. 2027'),__('80 guests')],[__('Los Angeles, Hollywood'),__('Sep. 2027'),__('200 guests')]] as $row)
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
            @foreach ([[\App\Support\Money::format(0, $currency), __('sign-up')], [$unlockCost, trans_choice('form.credits_per_accepted_request', $unlockCost)], [$openCities->count(), trans_choice('form.cities_available', $openCities->count())], [__(':hours h', ['hours' => $roundHours]), __('to accept a priority request')]] as $stat)
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
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('How it works') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('How to receive requests') }}</h2>
                <p class="text-[#6B665E] text-lg max-w-lg mx-auto">{{ __('You choose which requests you respond to.') }}</p>
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
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Why join') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A]">{{ __('Requests suited to your services') }}</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div class="col-span-2 bg-[#1A1A1A] rounded-2xl p-8 md:p-10 flex flex-col justify-between min-h-[220px] relative overflow-hidden">
                    <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 2px 2px, #F0D9B5 1px, transparent 0);background-size:24px 24px"></div>
                    <div class="relative">
                        <x-z.icon name="visibility" :filled="true" class="text-[#C8956C] text-3xl mb-5" />
                        <h3 class="font-serif text-2xl font-bold text-white mb-2">{{ __('View the details for free') }}</h3>
                        <p class="text-white/60 text-sm leading-relaxed">{{ __('Service, city, district, date, budget, guests and description, depending on what was provided.') }} {{ __('The first name and WhatsApp number appear after you accept.') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="tune" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Targeted requests') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __('You receive the requests that match your services and the cities you work in.') }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="lock_open" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Credits spent when you accept') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ $costLine }} {{ $noGuarantee }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm flex flex-col gap-4">
                    <x-z.icon name="schedule" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Priority requests') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __('A request is first offered to a small group of providers (:count at most). If nobody accepts, it opens to eligible providers in the city. The first to accept gets the contact details.', ['count' => $perRound]) }}</p>
                    </div>
                </div>
                <div class="col-span-2 md:col-span-1 bg-[#F0D9B5]/40 rounded-2xl p-7 border border-[#C8956C]/20 flex flex-col gap-4">
                    <x-z.icon name="support_agent" :filled="true" class="text-[#C8956C] text-2xl" />
                    <div>
                        <h3 class="font-semibold text-[#1A1A1A] mb-1">{{ __('Need help?') }}</h3>
                        <p class="text-[#6B665E] text-xs leading-relaxed">{{ __('This is a demo, so there is no support service. You can try the feedback form in the provider area.') }}</p>
                    </div>
                </div>
                <div class="col-span-2 bg-white rounded-2xl p-7 border border-[#D4CFC7]/50 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ __('Services offered') }}</p>
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
    <section id="pricing" class="py-24 md:py-32 px-5 md:px-8 scroll-mt-28">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-4">{{ __('Pricing') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] mb-4">{{ __('Choose your credit pack') }}</h2>
                <p class="text-[#6B665E] text-lg max-w-md mx-auto">{{ __('No subscription. You spend credits only on the requests you accept.') }}</p>
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
                        <p class="text-sm mb-1 {{ $p->is_popular ? 'text-white/60' : 'text-[#6B665E]' }}">{{ trans_choice('credits.unit', (int) $p->unlocks_count) }} · {{ trans_choice('form.credits_per_request', $unlockCost, ['count' => $unlockCost]) }}</p>
                        <p class="font-serif text-2xl font-bold mt-4 mb-0.5 {{ $p->is_popular ? 'text-[#C8956C]' : 'text-[#1A1A1A]' }}">{{ \App\Support\Money::format($p->price, $currency) }}</p>
                        <p class="text-xs mb-8 {{ $p->is_popular ? 'text-white/40' : 'text-[#9A958D]' }}">{{ __(':price per accepted request', ['price' => \App\Support\Money::format($perLead, $currency)]) }}</p>
                        <x-z.btn :href="route('provider.register')" :variant="$p->is_popular ? 'accent' : 'outline'" class="w-full justify-center mt-auto">
                            {{ $ctaLabel }}
                        </x-z.btn>
                    </div>
                @endforeach
            </div>
            <p class="text-center text-xs text-[#9A958D] mt-8">{{ __('Credits do not expire · Payment receipt available · Demo prices, no real payment') }}</p>
        </div>
    </section>

    {{-- ── FAQ ───────────────────────────────────────────── --}}
    <section class="py-24 px-5 md:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="font-serif text-4xl font-bold text-[#1A1A1A]">{{ __('Frequently asked questions') }}</h2>
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
                <p class="text-xs font-bold uppercase tracking-widest text-[#C8956C] mb-5">{{ __('Join us') }}</p>
                <h2 class="font-serif text-4xl md:text-5xl text-white font-bold mb-5 text-balance">
                    {{ __('Create your provider account') }}
                </h2>
                <p class="text-white/60 text-lg mb-10 max-w-lg mx-auto">{{ __('Free sign-up. Access to requests once your profile is approved.') }}</p>
                <x-z.btn :href="route('provider.register')" size="lg" variant="accent">
                    {{ $ctaLabel }} <x-z.icon name="arrow_forward" class="text-lg" />
                </x-z.btn>
            </div>
        </div>
    </section>

</div>
@endsection
