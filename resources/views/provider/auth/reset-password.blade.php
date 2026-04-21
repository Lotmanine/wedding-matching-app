<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('provider.auth.reset_title') }} — {{ __('provider.brand') }}</title>
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
<body class="antialiased text-gray-800 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-8">
        <h1 class="font-display text-2xl font-bold text-gray-900">{{ __('provider.auth.reset_title') }}</h1>

        <form method="POST" action="{{ route('provider.password.update') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.email') }}</label>
                <input id="email" name="email" type="email" required value="{{ old('email', $email ?? '') }}"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                />
                @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                />
                @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.password_confirm') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                />
            </div>

            <button type="submit" class="w-full py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm">
                {{ __('provider.auth.btn_reset') }}
            </button>
        </form>
    </div>
</body>
</html>
