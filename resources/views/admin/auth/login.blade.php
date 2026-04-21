<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('admin.auth.title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #0f172a; color: #f1f5f9; }
        .font-display { font-family: 'Playfair Display', serif; }
        .bg-brand { background: #1B5E20; }
        .hover\:bg-brand-dark:hover { background: #0f3a14; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-slate-800 rounded-2xl shadow-2xl p-8 border border-slate-700">
        <div class="flex items-center gap-2 mb-6">
            <span class="w-8 h-8 rounded-full bg-brand flex items-center justify-center">💍</span>
            <span class="font-display text-xl font-bold">{{ __('admin.brand') }}</span>
            <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-brand text-white uppercase tracking-widest">{{ __('admin.admin_badge') }}</span>
        </div>

        <h1 class="font-display text-2xl font-bold mb-1">{{ __('admin.auth.title') }}</h1>
        <p class="text-sm text-slate-400 mb-6">{{ __('admin.auth.sub') }}</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
                @foreach ($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">{{ __('admin.auth.email') }}</label>
                <input type="email" name="email" required autofocus value="{{ old('email') }}" class="w-full px-4 py-3 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 focus:border-emerald-500 focus:outline-none" />
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1">{{ __('admin.auth.password') }}</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg bg-slate-900 border border-slate-700 text-slate-100 focus:border-emerald-500 focus:outline-none" />
            </div>
            <button type="submit" class="w-full py-3 rounded-lg bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">
                {{ __('admin.auth.btn_login') }}
            </button>
        </form>
    </div>
</body>
</html>
