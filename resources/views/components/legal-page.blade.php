@props(['title', 'subtitle' => 'Zawaj Pro', 'updated' => null])

{{-- Full-bleed within layouts.couple's padded <main> --}}
<div class="-mx-4 sm:-mx-6 -my-8 sm:-my-12">
    {{-- Dark hero --}}
    <div class="bg-[#1A1A1A] pt-16 pb-16 px-5 md:px-8">
        <div class="max-w-3xl mx-auto">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white/40 hover:text-white/70 text-sm mb-6 transition-colors w-max">
                <x-z.icon name="arrow_back" class="text-base" /> Retour
            </a>
            <p class="text-[#C8956C] text-xs font-bold uppercase tracking-widest mb-3">Documents légaux</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-3">{{ $title }}</h1>
            <p class="text-white/50 text-sm">{{ $subtitle }}@if($updated) · Dernière mise à jour : {{ $updated }}@endif</p>
        </div>
    </div>

    {{-- Body --}}
    <div class="max-w-3xl mx-auto px-5 md:px-8 py-14">
        <div class="max-w-none space-y-8 text-[#1A1A1A]">
            {{ $slot }}
        </div>
        <div class="mt-14 pt-8 border-t border-[#D4CFC7]/40 flex flex-col sm:flex-row gap-4">
            <x-z.btn :href="route('home')" variant="outline" icon="arrow_back">Retour à l'accueil</x-z.btn>
        </div>
    </div>
</div>
