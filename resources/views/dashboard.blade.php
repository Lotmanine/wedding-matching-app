<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl border border-[#D4CFC7]/60 shadow-sm overflow-hidden">
                <div class="p-6 flex items-center gap-3 text-[#1A1A1A]">
                    <span class="material-symbols-outlined text-[#C8956C]" aria-hidden="true">check_circle</span>
                    {{ __('Vous êtes connecté !') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
