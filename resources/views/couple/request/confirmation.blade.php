@extends('layouts.couple')

@section('content')
    <div class="max-w-2xl mx-auto space-y-8">

        {{-- Hero --}}
        <div class="text-center">
            <div class="w-20 h-20 rounded-full bg-[#F0D9B5] flex items-center justify-center mx-auto mb-6 shadow-lg shadow-[#C8956C]/15">
                <x-z.icon name="celebration" :filled="true" class="text-4xl text-[#C8956C]" />
            </div>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-[#1A1A1A] leading-tight">
                {{ __('form.confirm_title', ['name' => e($wedRequest->couple_first_name)]) }}
            </h1>
            <p class="text-[#6B665E] text-lg max-w-md mx-auto mt-4">{{ __('form.confirm_subtitle') }}</p>
        </div>

        {{-- Reference UID --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6" x-data="{ copied: false }">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#9A958D] mb-3">{{ __('form.confirm_reference') }}</p>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <code class="font-serif text-2xl font-bold text-[#1A1A1A] tracking-wider select-all">{{ $wedRequest->wed_uid }}</code>
                <button
                    type="button"
                    @click="navigator.clipboard.writeText(@js($wedRequest->wed_uid)); copied = true; setTimeout(() => copied = false, 2000)"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 border"
                    :class="copied
                        ? 'bg-[#F0D9B5]/40 text-[#7A5530] border-[#C8956C]/40'
                        : 'bg-[#F7F4F0] text-[#6B665E] hover:bg-[#F0D9B5] hover:text-[#1A1A1A] border-[#D4CFC7]'"
                >
                    <span class="material-symbols-outlined text-base" x-text="copied ? 'check' : 'content_copy'" aria-hidden="true">content_copy</span>
                    <span x-show="!copied">{{ __('form.btn_copy') }}</span>
                    <span x-show="copied" x-cloak>{{ __('form.btn_copied') }}</span>
                </button>
            </div>
        </div>

        {{-- Next steps --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#9A958D] mb-6">{{ __('form.confirm_next_steps') }}</p>
            @php $steps = [__('form.confirm_step_1'), __('form.confirm_step_2'), __('form.confirm_step_3')]; @endphp
            <div class="space-y-5">
                @foreach ($steps as $i => $line)
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span class="w-10 h-10 rounded-full bg-[#F0D9B5] text-[#C8956C] text-sm font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</span>
                            @if ($i < count($steps) - 1)
                                <div class="w-px flex-1 bg-[#D4CFC7]/50 my-2"></div>
                            @endif
                        </div>
                        <p class="text-sm text-[#6B665E] leading-relaxed pt-2 pb-3">{{ $line }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Services requested --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ __('form.confirm_services') }}</p>
            <div class="space-y-3">
                @foreach ($wedRequest->serviceRequests as $sr)
                    <div class="flex items-center justify-between p-3 bg-[#F7F4F0] rounded-xl">
                        <span class="text-sm font-semibold text-[#1A1A1A]">{{ $sr->service_type?->label() ?? $sr->service_type }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#C8956C] animate-pulse"></span>
                            <span class="text-xs font-bold text-[#C8956C]">{{ __('form.confirm_status_searching') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Expiry / ANPDP notice --}}
        <div class="flex gap-3 px-4 py-3 bg-[#F7F4F0] rounded-xl border border-[#D4CFC7]/40">
            <x-z.icon name="shield" :filled="true" class="text-[#9A958D] text-lg shrink-0 mt-0.5" />
            <p class="text-xs text-[#9A958D] leading-relaxed">
                {{ __('form.confirm_expiry', ['date' => $wedRequest->expires_at->isoFormat('LL')]) }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            <x-z.btn :href="route('home')" variant="primary" icon="arrow_back" class="flex-1">{{ __('form.btn_home') }}</x-z.btn>
            <x-z.btn :href="route('couple.request.create')" variant="outline" class="flex-1">{{ __('form.nav_submit_request') }}</x-z.btn>
        </div>

    </div>
@endsection
