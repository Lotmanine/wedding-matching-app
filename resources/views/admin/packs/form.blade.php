@extends('layouts.admin')

@section('content')
    @php $isEdit = (bool) $pack?->exists; @endphp
    <div class="max-w-2xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.packs.index') }}" class="hover:text-brand">← Packs</a></nav>
        <h1 class="font-display text-2xl font-bold mb-4">{{ $isEdit ? 'Éditer le pack' : 'Nouveau pack' }}</h1>

        <form method="POST" action="{{ $isEdit ? route('admin.packs.update', $pack->id) : route('admin.packs.store') }}" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.packs.field_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $pack->name) }}" required maxlength="100" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.packs.field_count') }}</label>
                    <input type="number" name="unlocks_count" value="{{ old('unlocks_count', $pack->unlocks_count ?? 1) }}" min="1" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.packs.field_price') }}</label>
                    <input type="number" name="price_dzd" value="{{ old('price_dzd', $pack->price_dzd ?? 1000) }}" min="0" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('admin.packs.field_order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $pack->sort_order ?? 0) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_popular" value="0">
                <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $pack->is_popular)) class="rounded text-brand">
                {{ __('admin.packs.field_popular') }}
            </label>

            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $pack->active ?? true)) class="rounded text-brand">
                {{ __('admin.packs.field_active') }}
            </label>

            <div class="pt-3 border-t border-gray-100">
                <button class="px-5 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white font-semibold text-sm transition">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            </div>
        </form>
    </div>
@endsection
