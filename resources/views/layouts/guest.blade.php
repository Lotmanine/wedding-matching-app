<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Zawaj Pro') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#1A1A1A] antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 bg-[#FDFAF6]">
            <a href="{{ route('home') }}" class="font-serif text-2xl font-bold text-[#1A1A1A] hover:text-[#C8956C] transition-colors mb-1">{{ config('app.name', 'Zawaj Pro') }}</a>
            <p class="text-xs font-bold uppercase tracking-widest text-[#9A958D] mb-6">L'excellence pour votre mariage</p>

            <div class="w-full sm:max-w-md px-6 py-7 bg-white border border-[#D4CFC7]/60 shadow-sm sm:rounded-2xl">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-xs text-[#9A958D] hover:text-[#1A1A1A] transition-colors flex items-center gap-1">
                <x-z.icon name="arrow_back" class="text-sm" /> Retour au site
            </a>
        </div>
    </body>
</html>
