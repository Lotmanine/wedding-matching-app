@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> Utilisateurs</a></nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ $user->name }}</h1>
        <p class="text-sm text-[#6B665E] mt-0.5 mb-6">{{ $user->email }}</p>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6 space-y-4 text-sm">
            <dl class="grid grid-cols-2 gap-4">
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Rôles</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->roles->pluck('name')->join(', ') }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Statut</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->status }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Téléphone</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->phone ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">WhatsApp</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->whatsapp ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Wilaya</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->wilaya_code ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Dernière activité</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->last_active_at?->diffForHumans() ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Inscrit</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->created_at?->isoFormat('LL') }}</dd></div>
            </dl>

            @if ($user->hasRole('provider') && $user->providerProfile)
                <div class="mt-4 pt-4 border-t border-[#D4CFC7]/30">
                    <a href="{{ route('admin.providers.show', $user->id) }}" class="inline-flex items-center gap-1 text-[#C8956C] text-sm font-bold hover:underline">Voir profil prestataire <x-z.icon name="arrow_forward" class="text-base" /></a>
                </div>
            @endif
        </div>
    </div>
@endsection
