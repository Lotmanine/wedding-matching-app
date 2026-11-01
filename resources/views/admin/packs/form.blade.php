@extends('layouts.admin')

@section('content')
    @php $isEdit = (bool) $pack?->exists; @endphp
    <div class="max-w-2xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.packs.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('admin.nav.packs') }}</a></nav>
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-6">{{ $isEdit ? __('Edit pack') : __('admin.packs.create_btn') }}</h1>

        <form method="POST" action="{{ $isEdit ? route('admin.packs.update', $pack->id) : route('admin.packs.store') }}" class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8 space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.packs.field_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $pack->name) }}" required maxlength="100" class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
            </div>

            <div class="grid grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.packs.field_count') }}</label>
                    <input type="number" name="unlocks_count" value="{{ old('unlocks_count', $pack->unlocks_count ?? 1) }}" min="1" required class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.packs.field_price') }}</label>
                    <input type="number" name="price" value="{{ old('price', $pack->price ?? 99) }}" min="0" required class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.packs.field_order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $pack->sort_order ?? 0) }}" class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
            </div>

            <label class="flex items-center gap-2 text-sm text-[#1A1A1A]">
                <input type="hidden" name="is_popular" value="0">
                <input type="checkbox" name="is_popular" value="1" @checked(old('is_popular', $pack->is_popular)) class="rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30">
                {{ __('admin.packs.field_popular') }}
            </label>

            <label class="flex items-center gap-2 text-sm text-[#1A1A1A]">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $pack->active ?? true)) class="rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30">
                {{ __('admin.packs.field_active') }}
            </label>

            <div class="pt-4 border-t border-[#D4CFC7]/30 flex justify-end gap-3">
                <x-z.btn :href="route('admin.packs.index')" variant="outline" size="sm">{{ __('Cancel') }}</x-z.btn>
                <x-z.btn type="submit" variant="accent" size="sm">{{ $isEdit ? __('Save') : __('Create') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
