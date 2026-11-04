{{-- Public footer — clay/ink identity (ported from claude-design PublicFooter) --}}
@php
    $footerCities = \App\Models\City::active()->pluck('name');
    $footerCitiesText = $footerCities->join(', ', ' ' . __('and') . ' ');
@endphp
<footer class="bg-[#1A1A1A] pt-14 pb-8">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10 pb-12 border-b border-white/10">
            {{-- Brand --}}
            <div class="col-span-2 md:col-span-1">
                <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-white mb-3 hover:text-[#C8956C] transition-colors block">{{ __('form.brand') }}</a>
                <p class="text-sm text-white/40 leading-relaxed max-w-[200px]">{{ __('app.tagline') }}</p>
            </div>

            {{-- Couples --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Couples') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('couple.request.create') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('form.nav_submit_request') }}</a></li>
                    <li><a href="{{ route('home') }}#how-it-works" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('How it works') }}</a></li>
                    <li><a href="{{ route('home') }}#faq" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Frequently asked questions') }}</a></li>
                </ul>
            </div>

            {{-- Providers --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Providers') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('provider.landing') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('form.nav_for_providers') }}</a></li>
                    <li><a href="{{ route('provider.login') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Log in') }}</a></li>
                    <li><a href="{{ route('provider.landing') }}#pricing" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Credit pricing') }}</a></li>
                </ul>
            </div>

            {{-- Demo --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Demo') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('admin.login') }}" class="text-sm text-white/40 hover:text-white/70 transition-colors inline-flex items-center gap-1.5"><x-z.icon name="shield_person" class="text-sm" />{{ __('admin.admin_badge') }}</a></li>
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-white/30 mb-4">{{ __('Legal') }}</p>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('legal.privacy') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Privacy policy') }}</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-sm text-white/55 hover:text-white transition-colors">{{ __('Terms of use') }}</a></li>
                </ul>
            </div>
        </div>

        <p class="pt-7 text-xs text-white/55">{{ __('Portfolio demo project. Not a real business: no real providers, payments or bookings.') }}</p>
        <p class="pt-2 text-xs text-white/55"><a href="https://oldev.site/work/27-wedding-matching-app/" class="underline underline-offset-2 hover:text-white transition-colors">{{ __('Demo built by Lotfi Otmanine (OLDEV)') }}</a></p>

        <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-white/25">© {{ date('Y') }} {{ __('form.brand') }} · {{ __('All rights reserved') }}</p>
            <span class="text-xs text-white/25">{{ __('Available in :cities', ['cities' => $footerCitiesText]) }}</span>
        </div>
    </div>
</footer>
