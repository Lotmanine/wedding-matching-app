@extends('layouts.admin')

@section('content')
    @php $isEdit = (bool) $endpoint->exists; @endphp
    <div class="max-w-2xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.webhooks.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('admin.nav.webhooks') }}</a></nav>
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-6">{{ $isEdit ? __('Edit webhook') : __('New webhook') }}</h1>

        <form method="POST" action="{{ $isEdit ? route('admin.webhooks.update', $endpoint->id) : route('admin.webhooks.store') }}" class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8 space-y-5">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('Name') }}</label>
                <input type="text" name="name" required maxlength="100" value="{{ old('name', $endpoint->name) }}" class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('URL') }}</label>
                <input type="url" name="url" required value="{{ old('url', $endpoint->url) }}" placeholder="https://n8n.example.com/webhook/xxx" class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('HMAC secret') }}</label>
                <input type="text" name="secret" value="{{ old('secret', $endpoint->secret) }}" class="w-full px-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm font-mono outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                <p class="text-xs text-[#9A958D] mt-1.5">{{ __('Used to sign each delivery with HMAC-SHA256. Empty = keep the current secret.') }}</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-2">{{ __('Events to receive') }}</label>
                @php $subscribed = (array) old('events', $endpoint->events ?? []); @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    @foreach ($availableEvents as $ev)
                        <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-[#D4CFC7] has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/30 transition cursor-pointer">
                            <input type="checkbox" name="events[]" value="{{ $ev }}" @checked(in_array($ev, $subscribed, true)) class="rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30">
                            <span class="text-xs font-mono text-[#1A1A1A]">{{ $ev }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-[#1A1A1A]">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" @checked(old('active', $endpoint->active ?? true)) class="rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30">
                {{ __('Active') }}
            </label>

            <div class="pt-4 border-t border-[#D4CFC7]/30 flex justify-end gap-3">
                <x-z.btn :href="route('admin.webhooks.index')" variant="outline" size="sm">{{ __('Cancel') }}</x-z.btn>
                <x-z.btn type="submit" variant="accent" size="sm">{{ $isEdit ? __('Save') : __('Create') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
