{{-- EN / FR switch. $tone: "dark" on the black utility bar, "light" elsewhere. --}}
@php
    $tone = $tone ?? 'light';
    $current = app()->getLocale();
    $wrap = $tone === 'dark' ? 'border-white/20' : 'border-[#D4CFC7]';
    $on = $tone === 'dark' ? 'bg-white text-[#1A1A1A]' : 'bg-[#1A1A1A] text-white';
    $off = $tone === 'dark' ? 'text-white/70 hover:text-white' : 'text-[#6B665E] hover:text-[#1A1A1A]';
@endphp
<div class="inline-flex items-center rounded-full border {{ $wrap }} p-0.5 text-xs font-semibold leading-none" role="group" aria-label="Language / Langue">
    <a href="{{ route('locale.switch', 'en') }}" lang="en" hreflang="en" rel="nofollow"
        @if ($current === 'en') aria-current="true" @endif
        class="px-2.5 py-1 rounded-full transition-colors {{ $current === 'en' ? $on : $off }}">EN</a>
    <a href="{{ route('locale.switch', 'fr') }}" lang="fr" hreflang="fr" rel="nofollow"
        @if ($current === 'fr') aria-current="true" @endif
        class="px-2.5 py-1 rounded-full transition-colors {{ $current === 'fr' ? $on : $off }}">FR</a>
</div>
