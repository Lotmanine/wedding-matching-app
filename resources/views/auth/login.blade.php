<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Connexion') }}</h1>
        <p class="mt-1 text-sm text-[#6B665E]">{{ __('Accédez à votre espace.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Adresse e-mail') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('email')" class="text-xs text-[#A84936]" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label for="password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Mot de passe') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('password')" class="text-xs text-[#A84936]" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30" name="remember">
                <span class="ms-2 text-sm text-[#6B665E]">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>

        <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('Se connecter') }}</x-z.btn>

        @if (Route::has('password.request'))
            <div class="text-center">
                <a class="text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline" href="{{ route('password.request') }}">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>
