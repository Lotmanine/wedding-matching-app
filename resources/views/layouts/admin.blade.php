<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('admin.brand') . ' — Admin' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; background: #F7F5EF; }
        .font-display { font-family: 'Playfair Display', serif; }
        .bg-brand { background-color: #1B5E20; }
        .text-brand { color: #1B5E20; }
        .border-brand { border-color: #1B5E20; }
        .hover\:bg-brand-dark:hover { background-color: #0f3a14; }
        .bg-ink { background-color: #0f172a; }
        .text-ink { color: #0f172a; }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="min-h-screen flex flex-col sm:flex-row">

        <aside class="sm:w-60 sm:min-h-screen bg-ink text-gray-200 flex sm:flex-col">
            <div class="px-5 py-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-brand flex items-center justify-center text-white">💍</span>
                    <div>
                        <div class="font-display text-lg font-bold">{{ __('admin.brand') }}</div>
                        <div class="text-[10px] uppercase tracking-widest text-gray-400">{{ __('admin.admin_badge') }}</div>
                    </div>
                </a>
            </div>
            <nav class="flex-1 py-3 flex sm:flex-col gap-0.5 px-2 overflow-x-auto sm:overflow-x-visible">
                @php
                    $items = [
                        ['route' => 'admin.dashboard',          'label' => __('admin.nav.dashboard'),        'icon' => '📊'],
                        ['route' => 'admin.providers.index',    'label' => __('admin.nav.providers'),        'icon' => '🤝'],
                        ['route' => 'admin.requests.index',     'label' => __('admin.nav.requests'),         'icon' => '💍'],
                        ['route' => 'admin.service_requests.index', 'label' => __('admin.nav.service_requests'), 'icon' => '📋'],
                        ['route' => 'admin.packs.index',        'label' => __('admin.nav.packs'),            'icon' => '🎟️'],
                        ['route' => 'admin.purchases.index',    'label' => __('admin.nav.purchases'),        'icon' => '💰'],
                        ['route' => 'admin.users.index',        'label' => __('admin.nav.users'),            'icon' => '👥'],
                        ['route' => 'admin.logs.audit',         'label' => __('admin.nav.logs'),             'icon' => '📜'],
                        ['route' => 'admin.settings.index',     'label' => __('admin.nav.settings'),         'icon' => '⚙️'],
                    ];
                @endphp
                @foreach ($items as $item)
                    @php $active = request()->routeIs(str_replace('index', '*', $item['route'])) || request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" @class([
                        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap',
                        'bg-white/10 text-white' => $active,
                        'text-gray-300 hover:bg-white/5 hover:text-white' => ! $active,
                    ])>
                        <span>{{ $item['icon'] }}</span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="px-3 py-3 border-t border-white/10">
                @csrf
                <div class="px-2 mb-2 text-xs text-gray-400">
                    <div class="text-gray-100 font-medium">{{ auth()->user()?->name }}</div>
                    <div class="text-[10px]">{{ auth()->user()?->email }}</div>
                </div>
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-red-300 hover:bg-red-500/10 transition">
                    <span>🚪</span><span>{{ __('admin.nav.logout') }}</span>
                </button>
            </form>
        </aside>

        <main class="flex-1 px-4 sm:px-8 py-6 sm:py-8">
            @if (session('status'))
                <div class="mb-5 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-brand text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    @foreach ($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    @livewireScripts
</body>
</html>
