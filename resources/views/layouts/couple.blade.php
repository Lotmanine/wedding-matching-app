<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('form.brand') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased text-[#1A1A1A] bg-[#FDFAF6] min-h-screen flex flex-col">

    {{-- ─── Public navigation ─────────────────────────────── --}}
    <div x-data="{ open: false }" class="sticky top-0 z-50">
        {{-- Provider utility bar --}}
        <div class="bg-[#1A1A1A] py-2 px-5 md:px-8 flex items-center justify-end">
            <a href="{{ route('provider.login') }}" class="flex items-center gap-2 text-xs font-semibold text-white/70 hover:text-white transition-colors">
                <x-z.icon name="storefront" class="text-sm" />
                {{ __('form.nav_provider_area') }}
                <x-z.icon name="login" class="text-sm" />
            </a>
        </div>
        {{-- Main nav --}}
        <nav class="bg-[#FDFAF6]/95 backdrop-blur-md border-b border-[#D4CFC7]/40">
            <div class="max-w-7xl mx-auto px-5 md:px-8 py-3.5 flex items-center justify-between">
                <a href="{{ route('home') }}" class="font-serif text-[22px] font-bold text-[#1A1A1A] tracking-tight hover:text-[#C8956C] transition-colors">{{ __('form.brand') }}</a>
                <div class="hidden md:flex items-center gap-7">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-[#6B665E] hover:text-[#1A1A1A] transition-colors">{{ __('form.nav_for_couples') }}</a>
                    <a href="{{ route('couple.request.create') }}" class="text-sm font-medium {{ request()->routeIs('couple.request.*') ? 'text-[#C8956C] font-semibold' : 'text-[#6B665E] hover:text-[#1A1A1A]' }} transition-colors">{{ __('form.nav_submit_request') }}</a>
                    <a href="{{ route('provider.login') }}" class="text-sm font-medium text-[#6B665E] hover:text-[#1A1A1A] transition-colors">{{ __('form.nav_become_provider') }}</a>
                </div>
                <div class="hidden sm:flex items-center">
                    <x-z.btn :href="route('couple.request.create')" size="sm">{{ __('form.nav_cta') }}</x-z.btn>
                </div>
                <button class="md:hidden p-2 -mr-2" @click="open = !open" aria-label="Menu">
                    <span class="material-symbols-outlined text-[#1A1A1A] text-2xl" x-text="open ? 'close' : 'menu'">menu</span>
                </button>
            </div>
            <div x-show="open" x-cloak class="md:hidden bg-[#FDFAF6] border-t border-[#D4CFC7]/30 px-5 py-5 space-y-3">
                <a href="{{ route('home') }}" class="block text-sm font-medium text-[#6B665E] py-2">{{ __('form.nav_for_couples') }}</a>
                <a href="{{ route('couple.request.create') }}" class="block text-sm font-semibold text-[#1A1A1A] py-2">{{ __('form.nav_submit_request') }}</a>
                <a href="{{ route('provider.login') }}" class="block text-sm font-medium text-[#9A958D] py-2 flex items-center gap-2"><x-z.icon name="login" class="text-sm" />{{ __('form.nav_provider_area') }}</a>
                <div class="pt-2">
                    <x-z.btn :href="route('couple.request.create')" class="w-full justify-center">{{ __('form.nav_cta') }}</x-z.btn>
                </div>
            </div>
        </nav>
    </div>

    <main class="flex-1 px-4 sm:px-6 py-8 sm:py-12">
        @if (session('status'))
            <div class="max-w-4xl mx-auto mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    @include('partials.public-footer')

    @livewireScripts
</body>
</html>
