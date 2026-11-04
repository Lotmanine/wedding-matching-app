<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('provider.auth.login_title') }} · {{ __('provider.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-[#1A1A1A] min-h-screen flex flex-col bg-[#FDFAF6]">
    <header class="px-6 py-5 border-b border-[#D4CFC7]/40">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-[#1A1A1A] hover:text-[#C8956C] transition-colors">{{ __('provider.brand') }}</a>
            @include('partials.lang-switch')
        </div>
    </header>

    <main class="flex-1 px-4 py-12 flex flex-col items-center justify-center">
        <div class="w-full max-w-md bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-8 md:p-10">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('provider.auth.login_title') }}</h1>
            <p class="mt-1 text-sm text-[#6B665E]">{{ __('provider.auth.login_sub') }}</p>

            @if (session('status'))
                <div class="mt-4 p-3 rounded-lg bg-[#F0D9B5]/40 text-[#7A5530] text-sm border border-[#C8956C]/30">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('provider.login.store') }}" class="mt-6 space-y-5">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.email') }}</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="email" value="{{ old('email') }}"
                           class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                    />
                    @error('email') <p class="mt-1.5 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                    />
                    @error('password') <p class="mt-1.5 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30">
                        <span class="text-[#6B665E]">{{ __('provider.auth.remember_me') }}</span>
                    </label>
                    <a href="{{ route('provider.password.request') }}" class="font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline">{{ __('provider.auth.forgot_link') }}</a>
                </div>

                <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('provider.auth.btn_login') }}</x-z.btn>
            </form>

            @include('partials.demo-accounts', ['accounts' => ['provider', 'provider_fr']])

            <p class="mt-6 text-center text-sm text-[#6B665E]">
                {{ __('provider.auth.no_account') }}
                <a href="{{ route('provider.register') }}" class="font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline">{{ __('provider.auth.create_account') }}</a>
            </p>
        </div>

        <a href="{{ route('home') }}" class="mt-6 inline-flex items-center gap-1 text-sm text-[#9A958D] hover:text-[#6B665E] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('Back to the site') }}</a>
    </main>
@include('partials.portfolio-notice')
</body>
</html>
