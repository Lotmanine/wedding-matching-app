<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Vérifiez votre email') }}</h1>
        <p class="mt-2 text-sm text-[#6B665E] leading-relaxed">
            {{ __('Merci de votre inscription ! Avant de commencer, pourriez-vous vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer ? Si vous n\'avez pas reçu l\'email, nous vous en renverrons un avec plaisir.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-lg bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-sm font-medium text-[#7A5530]">
            {{ __('Un nouveau lien de vérification a été envoyé à l\'adresse email fournie lors de votre inscription.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-z.btn type="submit" variant="primary" size="md">{{ __('Renvoyer l\'email de vérification') }}</x-z.btn>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline">
                {{ __('Se déconnecter') }}
            </button>
        </form>
    </div>
</x-guest-layout>
