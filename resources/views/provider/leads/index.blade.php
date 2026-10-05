@extends('layouts.provider')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-8">
            <div>
                <h1 class="font-serif text-3xl font-bold text-[#1A1A1A]">
                    {{ __('provider.leads.title') }}
                </h1>
                @if ($canReceive)
                    <p class="text-sm text-[#6B665E] mt-1">
                        {{ trans_choice('provider.leads.count', $leads->total(), ['count' => $leads->total()]) }}
                    </p>
                @endif
            </div>
        </div>

        @if (! $canReceive)
            <div class="p-10 rounded-2xl bg-white border border-[#D4CFC7]/50 shadow-sm text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center">
                    <x-z.icon name="schedule" :filled="true" class="text-amber-600 text-3xl" />
                </div>
                <p class="mt-4 text-sm text-[#6B665E]">{{ __('provider.leads.empty_unverified') }}</p>
            </div>
        @else
            {{-- Filter bar --}}
            <form method="GET" action="{{ route('provider.leads.index') }}"
                  class="sticky top-0 lg:top-0 z-10 bg-white/95 backdrop-blur border border-[#D4CFC7]/50 rounded-xl shadow-sm p-4 mb-6 grid grid-cols-1 sm:grid-cols-5 gap-3">

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1.5">{{ __('provider.leads.filter_service') }}</label>
                    <select name="service_type" class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($serviceTypeOptions as $st)
                            <option value="{{ $st->value }}" @selected(($filters['service_type'] ?? '') === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1.5">{{ __('provider.leads.filter_wilaya') }}</label>
                    <select name="wilaya_code" class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($wilayaOptions as $w)
                            <option value="{{ $w->code }}" @selected(($filters['wilaya_code'] ?? '') === $w->code)>{{ app()->getLocale() === 'ar' ? $w->name_ar : $w->name_fr }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1.5">{{ __('provider.leads.filter_budget') }}</label>
                    <select name="budget_range" class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($budgetOptions as $b)
                            <option value="{{ $b->value }}" @selected(($filters['budget_range'] ?? '') === $b->value)>{{ $b->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1.5">{{ __('Tri') }}</label>
                    <select name="sort" class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="newest"       @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('provider.leads.sort_newest') }}</option>
                        <option value="oldest"       @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('provider.leads.sort_oldest') }}</option>
                        <option value="wedding_date" @selected(($filters['sort'] ?? '') === 'wedding_date')>{{ __('provider.leads.sort_wedding_date') }}</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <x-z.btn type="submit" variant="primary" size="md" icon="filter_list" class="flex-1 justify-center">
                        {{ __('Filtrer') }}
                    </x-z.btn>
                    @if (array_filter($filters))
                        <a href="{{ route('provider.leads.index') }}" class="w-10 h-[42px] rounded-lg border border-[#D4CFC7] text-[#9A958D] hover:text-[#A84936] hover:border-[#A84936]/40 flex items-center justify-center shrink-0 transition-colors" aria-label="{{ __('provider.leads.filter_reset') }}">
                            <x-z.icon name="close" class="text-lg" />
                        </a>
                    @endif
                </div>
            </form>

            @if ($leads->isEmpty())
                <div class="p-10 rounded-2xl bg-white border border-[#D4CFC7]/50 shadow-sm text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-[#F0D9B5] flex items-center justify-center">
                        <x-z.icon name="inbox" :filled="true" class="text-[#C8956C] text-3xl" />
                    </div>
                    @if (array_filter($filters))
                        <p class="mt-4 text-[#1A1A1A] font-semibold">{{ __('provider.leads.empty_filters') }}</p>
                        <a href="{{ route('provider.leads.index') }}" class="mt-3 inline-block text-sm text-[#C8956C] font-semibold hover:underline">
                            {{ __('provider.leads.filter_reset') }}
                        </a>
                    @else
                        <h2 class="mt-4 font-serif text-xl font-bold text-[#1A1A1A]">{{ __('provider.leads.empty_no_leads') }}</h2>
                        <p class="mt-2 text-sm text-[#6B665E]">{{ __('provider.leads.empty_sub_default') }}</p>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($leads as $lead)
                        <x-lead-card :lead="$lead" />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $leads->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
