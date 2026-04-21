<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('provider.auth.login_title') }} — {{ __('provider.brand') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <span class="inline-block w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center">💍</span>
                <span class="font-display text-xl font-bold text-brand">{{ __('provider.brand') }}</span>
            </a>
        </div>
    </header>

    <main class="flex-1 px-4 py-12 flex items-center justify-center">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-8">
            <h1 class="font-display text-2xl font-bold text-gray-900">{{ __('provider.auth.login_title') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('provider.auth.login_sub') }}</p>

            @if (session('status'))
                <div class="mt-4 p-3 rounded-lg bg-emerald-50 text-brand text-sm border border-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('provider.login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.email') }}</label>
                    <input id="email" name="email" type="email" required autofocus autocomplete="email" value="{{ old('email') }}"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                    />
                    @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                    />
                    @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="remember" class="rounded text-brand focus:ring-emerald-100">
                        <span class="text-gray-600">{{ __('provider.auth.remember_me') }}</span>
                    </label>
                    <a href="{{ route('provider.password.request') }}" class="text-brand hover:underline">{{ __('provider.auth.forgot_link') }}</a>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition">
                    {{ __('provider.auth.btn_login') }}
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                {{ __('provider.auth.no_account') }}
                <a href="{{ route('provider.register') }}" class="text-brand font-semibold hover:underline">{{ __('provider.auth.create_account') }}</a>
            </p>
        </div>
    </main>
</body>
</html>
