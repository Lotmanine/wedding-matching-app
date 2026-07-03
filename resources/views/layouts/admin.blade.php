<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('admin.brand') . ' · Admin' }}</title>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased text-[#1A1A1A]">

    @php
        $items = [
            ['route' => 'admin.dashboard',            'pattern' => 'admin.dashboard',         'icon' => 'analytics',     'label' => __('admin.nav.dashboard')],
            ['route' => 'admin.requests.index',       'pattern' => 'admin.requests.*',        'icon' => 'inbox',         'label' => __('admin.nav.requests')],
            ['route' => 'admin.service_requests.index','pattern' => 'admin.service_requests.*','icon' => 'list_alt',      'label' => __('admin.nav.service_requests')],
            ['route' => 'admin.providers.index',      'pattern' => 'admin.providers.*',       'icon' => 'storefront',    'label' => __('admin.nav.providers')],
            ['route' => 'admin.packs.index',          'pattern' => 'admin.packs.*',           'icon' => 'inventory_2',   'label' => __('admin.nav.packs'),      'admin_only' => true],
            ['route' => 'admin.purchases.index',      'pattern' => 'admin.purchases.*',       'icon' => 'payments',      'label' => __('admin.nav.purchases')],
            ['route' => 'admin.disputes.index',       'pattern' => 'admin.disputes.*',        'icon' => 'flag',          'label' => 'Litiges'],
            ['route' => 'admin.payments.edit',        'pattern' => 'admin.payments.*',        'icon' => 'account_balance','label' => 'Paiements',               'admin_only' => true],
            ['route' => 'admin.catalog.edit',         'pattern' => 'admin.catalog.*',         'icon' => 'category',      'label' => 'Catalogue',                'admin_only' => true],
            ['route' => 'admin.users.index',          'pattern' => 'admin.users.*',           'icon' => 'group',         'label' => __('admin.nav.users'),      'admin_only' => true],
            ['route' => 'admin.logs.audit',           'pattern' => 'admin.logs.*',            'icon' => 'receipt_long',  'label' => __('admin.nav.logs'),       'admin_only' => true],
            ['route' => 'admin.webhooks.index',       'pattern' => 'admin.webhooks.*',        'icon' => 'webhook',       'label' => 'Webhooks',                 'admin_only' => true],
            ['route' => 'admin.settings.index',       'pattern' => 'admin.settings.*',        'icon' => 'settings',      'label' => __('admin.nav.settings'),   'admin_only' => true],
        ];

        // Managers only see the operations tabs — admin-only sections are
        // hidden here AND blocked by route middleware (defence in depth).
        if (! auth()->user()?->hasRole('admin')) {
            $items = array_values(array_filter($items, fn ($i) => empty($i['admin_only'])));
        }
    @endphp

    <div class="min-h-screen bg-[#F7F4F0] flex">

        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex flex-col w-56 bg-[#1A1A1A] sticky top-0 h-screen shrink-0">
            <div class="px-5 py-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="font-serif text-lg font-bold text-white">{{ __('admin.brand') }}</a>
                <div class="text-[10px] text-[#C8956C] font-bold uppercase tracking-wider mt-0.5">{{ __('admin.admin_badge') }}</div>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                @foreach ($items as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all text-left {{ $active ? 'bg-[#C8956C] text-white font-semibold' : 'text-white/60 hover:text-white hover:bg-white/10 font-medium' }}">
                        <x-z.icon :name="$item['icon']" :filled="$active" class="text-lg" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="px-4 py-4 border-t border-white/10">
                @csrf
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#C8956C] flex items-center justify-center text-xs font-bold text-white shrink-0 uppercase">{{ mb_substr(auth()->user()?->name ?? 'A', 0, 1) }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()?->name }}</p>
                        <button type="submit" class="text-[10px] text-white/40 hover:text-white/80 transition-colors">{{ __('admin.nav.logout') }}</button>
                    </div>
                </div>
            </form>
        </aside>

        <main class="flex-1 min-w-0">
            {{-- Mobile top bar --}}
            <header class="lg:hidden bg-[#1A1A1A] px-5 py-3.5 flex items-center justify-between sticky top-0 z-40 overflow-x-auto">
                <span class="font-serif font-bold text-white text-lg shrink-0 mr-4">{{ __('admin.brand') }}</span>
                <div class="flex items-center gap-4">
                    @foreach ($items as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}" class="shrink-0 {{ $active ? 'text-[#C8956C]' : 'text-white/50 hover:text-white' }} transition-colors">
                            <x-z.icon :name="$item['icon']" :filled="$active" class="text-xl" />
                        </a>
                    @endforeach
                </div>
            </header>

            <div class="px-4 sm:px-8 py-6 sm:py-8">
                @if (session('status'))
                    <div class="mb-5 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-100 text-[#A84936] text-sm">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-100 text-[#A84936] text-sm">
                        @foreach ($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
