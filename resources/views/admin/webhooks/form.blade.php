@extends('layouts.admin')

@section('content')
    @php $isEdit = (bool) $endpoint->exists; @endphp
    <div class="max-w-2xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.webhooks.index') }}" class="hover:text-brand">← Webhooks</a></nav>
        <h1 class="font-display text-2xl font-bold mb-4">{{ $isEdit ? 'Éditer' : 'Nouveau webhook' }}</h1>

        <form method="POST" action="{{ $isEdit ? route('admin.webhooks.update', $endpoint->id) : route('admin.webhooks.store') }}" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium mb-1">Nom</label>
                <input type="text" name="name" required maxlength="100" value="{{ old('name', $endpoint->name) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">URL</label>
                <input type="url" name="url" required value="{{ old('url', $endpoint->url) }}" placeholder="https://n8n.example.com/webhook/xxx" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Secret HMAC</label>
                <input type="text" name="secret" value="{{ old('secret', $endpoint->secret) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm font-mono" />
                <p class="text-xs text-gray-400 mt-1">Utilisé pour signer chaque livraison via HMAC-SHA256. Vide = garder le secret actuel.</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Événements à recevoir</label>
                @php $subscribed = (array) old('events', $endpoint->events ?? []); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    @foreach ($availableEvents as $ev)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50 transition cursor-pointer">
                            <input type="checkbox" name="events[]" value="{{ $ev }}" @checked(in_array($ev, $subscribed, true)) class="rounded text-brand">
                            <span class="text-xs font-mono">{{ $ev }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $endpoint->active ?? true)) class="rounded text-brand">
                Actif
            </label>

            <div class="pt-3 border-t border-gray-100">
                <button class="px-5 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            </div>
        </form>
    </div>
@endsection
