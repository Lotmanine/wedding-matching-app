<section>
    <header>
        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">
            {{ __('Modifier le mot de passe') }}
        </h2>

        <p class="mt-1 text-sm text-[#6B665E]">
            {{ __('Assurez-vous d\'utiliser un mot de passe long et aléatoire pour rester en sécurité.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-1.5">
            <label for="update_password_current_password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Mot de passe actuel') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="text-xs text-[#A84936]" />
        </div>

        <div class="space-y-1.5">
            <label for="update_password_password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Nouveau mot de passe') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="text-xs text-[#A84936]" />
        </div>

        <div class="space-y-1.5">
            <label for="update_password_password_confirmation" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Confirmer le mot de passe') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="text-xs text-[#A84936]" />
        </div>

        <div class="flex items-center gap-4">
            <x-z.btn type="submit" variant="primary" size="md">{{ __('Enregistrer') }}</x-z.btn>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-[#6B665E]"
                >{{ __('Enregistré.') }}</p>
            @endif
        </div>
    </form>
</section>
