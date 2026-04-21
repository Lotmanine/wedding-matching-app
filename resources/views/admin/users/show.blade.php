@extends('layouts.admin')

@section('content')
    <div class="max-w-3xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.users.index') }}" class="hover:text-brand">← Utilisateurs</a></nav>

        <h1 class="font-display text-2xl font-bold">{{ $user->name }}</h1>
        <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3 text-sm">
            <dl class="grid grid-cols-2 gap-3">
                <div><dt class="text-xs uppercase text-gray-500">Rôles</dt><dd>{{ $user->roles->pluck('name')->join(', ') }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Statut</dt><dd>{{ $user->status }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Téléphone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">WhatsApp</dt><dd>{{ $user->whatsapp ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Wilaya</dt><dd>{{ $user->wilaya_code ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Dernière activité</dt><dd>{{ $user->last_active_at?->diffForHumans() ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase text-gray-500">Inscrit</dt><dd>{{ $user->created_at?->isoFormat('LL') }}</dd></div>
            </dl>

            @if ($user->hasRole('provider') && $user->providerProfile)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ route('admin.providers.show', $user->id) }}" class="text-brand text-sm font-semibold hover:underline">Voir profil prestataire →</a>
                </div>
            @endif
        </div>
    </div>
@endsection
