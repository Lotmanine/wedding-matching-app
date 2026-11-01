<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('provider.auth.register_title') }} · {{ __('provider.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-[#1A1A1A] min-h-screen flex flex-col bg-[#FDFAF6]">
    <header class="px-6 py-5 border-b border-[#D4CFC7]/40">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-[#1A1A1A] hover:text-[#C8956C] transition-colors">{{ __('provider.brand') }}</a>
            <div class="flex items-center gap-4">
                <a href="{{ route('provider.login') }}" class="hidden sm:inline text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors">
                    {{ __('provider.auth.has_account') }} <strong class="text-[#C8956C]">{{ __('provider.auth.btn_login') }}</strong>
                </a>
                @include('partials.lang-switch')
            </div>
        </div>
    </header>

    <main class="flex-1 px-4 py-10">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="font-serif text-3xl sm:text-4xl font-bold text-[#1A1A1A]">{{ __('provider.auth.register_title') }}</h1>
                <p class="mt-2 text-[#6B665E]">{{ __('provider.auth.register_sub') }}</p>
            </div>

            @livewire('provider.registration-form')
        </div>
    </main>

    @livewireScripts
</body>
</html>
