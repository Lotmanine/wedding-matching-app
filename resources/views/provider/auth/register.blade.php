<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('provider.auth.register_title') }} — {{ __('provider.brand') }}</title>
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
        .hover\:bg-brand-dark:hover { background-color: #0f3a14; }
    </style>
</head>
<body class="antialiased text-gray-800 min-h-screen flex flex-col">
    <header class="px-6 py-5 border-b border-emerald-100/60">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="inline-block w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center">💍</span>
                <span class="font-display text-xl font-bold text-brand">{{ __('provider.brand') }}</span>
            </a>
            <a href="{{ route('provider.login') }}" class="text-sm text-gray-600 hover:text-brand">
                {{ __('provider.auth.has_account') }} <strong>{{ __('provider.auth.btn_login') }}</strong>
            </a>
        </div>
    </header>

    <main class="flex-1 px-4 py-10">
        <div class="max-w-3xl mx-auto">
            <div class="mb-8 text-center">
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900">{{ __('provider.auth.register_title') }}</h1>
                <p class="mt-2 text-gray-500">{{ __('provider.auth.register_sub') }}</p>
            </div>

            @livewire('provider.registration-form')
        </div>
    </main>

    @livewireScripts
</body>
</html>
