<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Mot de passe oublié') }}</h1>
        <p class="mt-2 text-sm text-[#6B665E] leading-relaxed">
            {{ __('Pas de souci. Indiquez-nous votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('email')" class="text-xs text-[#A84936]" />
        </div>

        <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('Envoyer le lien de réinitialisation') }}</x-z.btn>

        <div class="text-center">
            <a class="text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline" href="{{ route('login') }}">
                {{ __('Retour à la connexion') }}
            </a>
        </div>
    </form>
</x-guest-layout>
