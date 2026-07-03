<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('provider.auth.reset_title') }} · {{ __('provider.brand') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-[#1A1A1A] min-h-screen flex items-center justify-center px-4 bg-[#FDFAF6]">
    <div class="w-full max-w-md bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-8 md:p-10">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('provider.auth.reset_title') }}</h1>

        <form method="POST" action="{{ route('provider.password.update') }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.email') }}</label>
                <input id="email" name="email" type="email" required value="{{ old('email', $email ?? '') }}"
                       class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                />
                @error('email') <p class="mt-1.5 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                />
                @error('password') <p class="mt-1.5 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('provider.auth.password_confirm') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                />
            </div>

            <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('provider.auth.btn_reset') }}</x-z.btn>
        </form>
    </div>
</body>
</html>
