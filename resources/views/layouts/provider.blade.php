<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('provider.brand') . ' — ' . __('provider.dashboard_title') }}</title>

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
        .hover\:bg-brand-dark:hover { background-color: #0f3a14; }
        .text-gold { color: #D4A843; }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="min-h-screen flex flex-col sm:flex-row">

        {{-- Sidebar --}}
        <aside class="sm:w-64 sm:min-h-screen bg-white border-b sm:border-b-0 sm:border-r border-emerald-100/70 flex sm:flex-col">
            <div class="px-5 py-5 border-b border-emerald-100/70 flex items-center gap-2">
                <span class="inline-block w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white text-sm">💍</span>
                <a href="{{ route('home') }}" class="font-display text-lg font-bold text-brand">{{ __('provider.brand') }}</a>
            </div>
            <nav class="flex-1 py-4 flex sm:flex-col gap-1 px-2 sm:px-4 overflow-x-auto sm:overflow-x-visible">
                @php
                    $links = [
                        ['route' => 'provider.dashboard',    'pattern' => 'provider.dashboard',    'label' => __('provider.nav.dashboard'), 'icon' => '🏠'],
                        ['route' => 'provider.leads.index',  'pattern' => 'provider.leads.*',      'label' => __('provider.nav.leads'),     'icon' => '📥'],
                        ['route' => 'provider.profile.edit', 'pattern' => 'provider.profile.*',    'label' => __('provider.nav.profile'),   'icon' => '👤'],
                    ];
                @endphp
                @foreach ($links as $link)
                    @php $active = request()->routeIs($link['pattern']); @endphp
                    <a
                        href="{{ route($link['route']) }}"
                        @class([
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition whitespace-nowrap',
                            'bg-emerald-50 text-brand' => $active,
                            'text-gray-600 hover:bg-emerald-50/60 hover:text-brand' => ! $active,
                        ])
                    >
                        <span>{{ $link['icon'] }}</span>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
                <span class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 whitespace-nowrap" title="Bientôt">
                    <span>💳</span><span>{{ __('provider.nav.credits') }}</span>
                </span>
                <span class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 whitespace-nowrap" title="Bientôt">
                    <span>📜</span><span>{{ __('provider.nav.history') }}</span>
                </span>
            </nav>
            <form method="POST" action="{{ route('provider.logout') }}" class="px-4 py-4 border-t border-emerald-100/70">
                @csrf
                <button type="submit" class="w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
                    <span>🚪</span><span>{{ __('provider.nav.logout') }}</span>
                </button>
            </form>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 px-4 sm:px-8 py-6 sm:py-10">
            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-brand text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    @livewireScripts
</body>
</html>
