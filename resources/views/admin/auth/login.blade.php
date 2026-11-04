<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('admin.auth.title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-white min-h-screen flex flex-col items-center justify-center px-5 bg-[#1A1A1A]">
    <div class="mb-10 text-center">
        <div class="font-serif text-3xl font-bold text-white mb-1">{{ __('admin.brand') }}</div>
        <div class="text-xs text-[#C8956C] font-bold uppercase tracking-widest">{{ __('admin.admin_badge') }}</div>
    </div>

    <div class="w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl p-8 space-y-5 backdrop-blur-sm">
        <h1 class="font-serif text-xl font-bold text-white">{{ __('admin.auth.title') }}</h1>
        <p class="text-sm text-white/50 -mt-2">{{ __('admin.auth.sub') }}</p>

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-[#A84936]/15 border border-[#A84936]/40 text-red-200 text-sm">
                @foreach ($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-white/60">{{ __('admin.auth.email') }}</label>
                <input type="email" name="email" required autofocus value="{{ old('email') }}"
                       class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/10 text-white placeholder-white/30 outline-none text-sm transition-colors focus:border-[#C8956C]/60 focus:ring-2 focus:ring-[#C8956C]/15" />
            </div>
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-white/60">{{ __('admin.auth.password') }}</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/10 text-white placeholder-white/30 outline-none text-sm transition-colors focus:border-[#C8956C]/60 focus:ring-2 focus:ring-[#C8956C]/15" />
            </div>
            <button type="submit"
                    class="w-full py-3 mt-2 bg-[#C8956C] hover:bg-[#B8845B] text-white font-bold rounded-lg transition-all active:scale-[0.98] text-sm">
                {{ __('admin.auth.btn_login') }}
            </button>
        </form>

            @include('partials.demo-accounts', ['accounts' => ['admin']])
    </div>

    <a href="{{ route('home') }}" class="mt-8 inline-flex items-center gap-1 text-xs text-white/30 hover:text-white/60 transition-colors"><x-z.icon name="arrow_back" class="text-sm" /> {{ __('Back to site') }}</a>
@include('partials.portfolio-notice')
</body>
</html>
