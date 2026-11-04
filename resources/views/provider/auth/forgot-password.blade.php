<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('provider.auth.forgot_title') }} · {{ __('provider.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-[#1A1A1A] min-h-screen flex items-center justify-center px-4 bg-[#FDFAF6]">
    <div class="absolute top-4 end-4">@include('partials.lang-switch')</div>
    <div class="w-full max-w-md bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-8 md:p-10">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('provider.auth.forgot_title') }}</h1>
        <p class="mt-1 text-sm text-[#6B665E]">{{ __('provider.auth.forgot_sub') }}</p>

        @if (session('status'))
            <div class="mt-4 p-3 rounded-lg bg-[#F0D9B5]/40 text-[#7A5530] text-sm border border-[#C8956C]/30">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('provider.password.email') }}" class="mt-6 space-y-5">
            @csrf
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.email') }}</label>
                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                       class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                />
                @error('email') <p class="mt-1.5 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('provider.auth.btn_send_link') }}</x-z.btn>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('provider.login') }}" class="inline-flex items-center gap-1 text-[#9A958D] hover:text-[#6B665E] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('provider.auth.btn_login') }}</a>
        </p>
    </div>
@include('partials.portfolio-notice')
</body>
</html>
