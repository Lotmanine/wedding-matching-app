<section class="space-y-6">
    <header>
        <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">
            {{ __('Supprimer le compte') }}
        </h2>

        <p class="mt-1 text-sm text-[#6B665E]">
            {{ __('Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Avant de supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.') }}
        </p>
    </header>

    <x-z.btn
        variant="danger"
        size="md"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Supprimer le compte') }}</x-z.btn>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-serif text-lg font-bold text-[#1A1A1A]">
                {{ __('Êtes-vous sûr de vouloir supprimer votre compte ?') }}
            </h2>

            <p class="mt-1 text-sm text-[#6B665E]">
                {{ __('Une fois votre compte supprimé, toutes ses ressources et données seront définitivement effacées. Veuillez saisir votre mot de passe pour confirmer la suppression définitive de votre compte.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">{{ __('Mot de passe') }}</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    placeholder="{{ __('Mot de passe') }}"
                    class="mt-1 block w-3/4 px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-xs text-[#A84936]" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-z.btn variant="outline" size="md" x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-z.btn>

                <x-z.btn type="submit" variant="danger" size="md">
                    {{ __('Supprimer le compte') }}
                </x-z.btn>
            </div>
        </form>
    </x-modal>
</section>
