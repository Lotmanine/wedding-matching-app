<section>
    <header>
        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">
            {{ __('Informations du profil') }}
        </h2>

        <p class="mt-1 text-sm text-[#6B665E]">
            {{ __("Mettez à jour les informations de votre compte et votre adresse email.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Nom') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error class="text-xs text-[#A84936]" :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error class="text-xs text-[#A84936]" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-[#1A1A1A]">
                        {{ __('Votre adresse email n\'est pas vérifiée.') }}

                        <button form="send-verification" class="underline text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] focus:outline-none focus:ring-2 focus:ring-[#C8956C]/30 rounded-md">
                            {{ __('Cliquez ici pour renvoyer l\'email de vérification.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-[#7A5530]">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse email.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-z.btn type="submit" variant="primary" size="md">{{ __('Enregistrer') }}</x-z.btn>

            @if (session('status') === 'profile-updated')
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
