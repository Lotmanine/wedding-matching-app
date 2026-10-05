<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('provider.brand') . ' · ' . __('provider.dashboard_title') }}</title>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased text-[#1A1A1A]">

    @php
        $user = auth()->user();
        $profile = $user?->providerProfile;
        $creditBalance = $profile?->credit_balance ?? 0;
        $unlockCost = (int) config('zawaj.credits.unlock_cost', 1);
        $initials = \Illuminate\Support\Str::of($profile?->business_name ?? $user?->name ?? 'PR')
            ->explode(' ')->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
        $nav = [
            ['route' => 'provider.dashboard',       'pattern' => 'provider.dashboard',    'icon' => 'dashboard',       'label' => __('provider.nav.dashboard')],
            ['route' => 'provider.leads.index',     'pattern' => 'provider.leads.index',  'icon' => 'inbox',           'label' => __('provider.nav.leads')],
            ['route' => 'provider.leads.accepted',  'pattern' => 'provider.leads.accepted','icon' => 'workspace_premium','label' => 'Acceptées'],
            ['route' => 'provider.credits.index',   'pattern' => 'provider.credits.index','icon' => 'toll',            'label' => __('provider.nav.credits')],
            ['route' => 'provider.credits.history', 'pattern' => 'provider.credits.history','icon' => 'history',       'label' => __('provider.nav.history')],
            ['route' => 'provider.profile.edit',    'pattern' => 'provider.profile.*',    'icon' => 'manage_accounts','label' => __('provider.nav.profile')],
            ['route' => 'provider.feedback.create', 'pattern' => 'provider.feedback.*',   'icon' => 'feedback',        'label' => 'Feedback'],
        ];
    @endphp

    <div class="min-h-screen bg-[#FDFAF6] flex">

        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex flex-col w-64 bg-white border-r border-[#D4CFC7]/40 sticky top-0 h-screen shrink-0">
            <div class="px-6 py-5 border-b border-[#D4CFC7]/30">
                <a href="{{ route('home') }}" class="font-serif text-xl font-bold text-[#1A1A1A] hover:text-[#C8956C] transition-colors">{{ __('provider.brand') }}</a>
                <p class="text-[11px] font-bold uppercase tracking-widest text-[#9A958D] mt-1">{{ __('provider.nav.area') }}</p>
            </div>
            <nav class="flex-1 px-3 py-5 space-y-0.5 overflow-y-auto">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all text-left {{ $active ? 'bg-[#F0D9B5] text-[#1A1A1A] font-semibold' : 'text-[#6B665E] hover:bg-[#F7F4F0] hover:text-[#1A1A1A]' }}">
                        <x-z.icon :name="$item['icon']" :filled="$active" class="text-xl" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="px-4 pb-4">
                <div class="bg-gradient-to-br from-[#F0D9B5]/60 to-[#FDFAF6] rounded-xl p-4 border border-[#C8956C]/20 mb-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1">{{ __('provider.nav.credit_balance') }}</p>
                    <p class="text-3xl font-serif font-bold text-[#1A1A1A]">{{ $creditBalance }}</p>
                    <p class="text-xs text-[#6B665E] mb-3">{{ __('provider.nav.credits_available') }}</p>
                    <x-z.btn :href="route('provider.credits.index')" size="xs" variant="outline" class="w-full justify-center">{{ __('provider.nav.recharge') }}</x-z.btn>
                </div>
                <form method="POST" action="{{ route('provider.logout') }}">
                    @csrf
                    <div class="flex items-center gap-3 px-1">
                        <div class="w-9 h-9 rounded-full bg-[#F0D9B5] flex items-center justify-center text-sm font-bold text-[#1A1A1A] shrink-0 uppercase">{{ $initials }}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-[#1A1A1A] truncate">{{ $profile?->business_name ?? $user?->name }}</p>
                            <button type="submit" class="text-xs text-[#9A958D] hover:text-[#A84936] transition-colors">{{ __('provider.nav.logout') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-w-0 pb-20 lg:pb-0">
            {{-- Mobile top bar --}}
            <header class="lg:hidden sticky top-0 z-40 bg-white border-b border-[#D4CFC7]/30 px-4 py-3.5 flex items-center justify-between">
                <a href="{{ route('home') }}" class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('provider.brand') }}</a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('provider.credits.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F0D9B5] rounded-full text-xs font-bold text-[#1A1A1A]">
                        <x-z.icon name="toll" class="text-sm" /> {{ $creditBalance }}
                    </a>
                    <div class="w-8 h-8 rounded-full bg-[#F0D9B5] flex items-center justify-center text-xs font-bold text-[#1A1A1A] uppercase">{{ $initials }}</div>
                </div>
            </header>

            <div class="px-4 sm:px-8 py-6 sm:py-10">
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>

            {{-- Mobile bottom nav --}}
            <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white border-t border-[#D4CFC7]/30 flex z-40">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['pattern']); @endphp
                    <a href="{{ route($item['route']) }}" class="flex-1 flex flex-col items-center py-2.5 gap-0.5 transition-colors {{ $active ? 'text-[#C8956C]' : 'text-[#9A958D]' }}">
                        <x-z.icon :name="$item['icon']" :filled="$active" class="text-[22px]" />
                        <span class="text-[9px] font-bold uppercase tracking-wide">{{ \Illuminate\Support\Str::of($item['label'])->explode(' ')->first() }}</span>
                    </a>
                @endforeach
            </nav>
        </main>
    </div>

    @livewireScripts
</body>
</html>
