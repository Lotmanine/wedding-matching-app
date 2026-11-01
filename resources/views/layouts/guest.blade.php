<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('form.brand') }}</title>


        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#1A1A1A] antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 bg-[#FDFAF6] relative">
            <div class="absolute top-4 end-4">@include('partials.lang-switch')</div>
            <a href="{{ route('home') }}" class="font-serif text-2xl font-bold text-[#1A1A1A] hover:text-[#C8956C] transition-colors mb-1">{{ __('form.brand') }}</a>
            <p class="text-xs font-bold uppercase tracking-widest text-[#9A958D] mb-6">{{ __('form.brand_tagline') }}</p>

            <div class="w-full sm:max-w-md px-6 py-7 bg-white border border-[#D4CFC7]/60 shadow-sm sm:rounded-2xl">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-xs text-[#9A958D] hover:text-[#1A1A1A] transition-colors flex items-center gap-1">
                <x-z.icon name="arrow_back" class="text-sm" /> {{ __('Back to site') }}
            </a>
        </div>
    @include('partials.portfolio-notice')
    </body>
</html>
