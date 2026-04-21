<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('form.brand') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #FEFDF8; }
        .font-display { font-family: 'Playfair Display', serif; }
        .bg-brand { background-color: #1B5E20; }
        .text-brand { color: #1B5E20; }
        .border-brand { border-color: #1B5E20; }
        .bg-gold { background-color: #D4A843; }
        .text-gold { color: #D4A843; }
        .hover\:bg-brand-dark:hover { background-color: #0f3a14; }
        .ring-brand:focus { --tw-ring-color: #1B5E20; }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="min-h-screen flex flex-col">

        <header class="px-6 sm:px-10 py-5 border-b border-emerald-100/60 bg-white/70">
            <div class="max-w-4xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="inline-block w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white text-base">💍</span>
                    <span class="font-display text-xl font-bold text-brand tracking-tight">{{ __('form.brand') }}</span>
                </a>
                <span class="text-xs text-gray-400 hidden sm:inline">{{ __('form.brand_tagline') }}</span>
            </div>
        </header>

        <main class="flex-1 px-4 sm:px-6 py-8 sm:py-12">
            @yield('content')
        </main>

        <footer class="border-t border-emerald-100/60 px-6 py-6 bg-white/60">
            <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
                <p>{{ __('form.footer_copyright', ['year' => date('Y')]) }}</p>
                <a href="#" class="hover:text-brand">{{ __('form.footer_privacy') }}</a>
            </div>
        </footer>

    </div>

    @livewireScripts
</body>
</html>
