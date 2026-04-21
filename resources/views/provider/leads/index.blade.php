@extends('layouts.provider')

@section('content')
    <div class="max-w-6xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">
                {{ __('provider.leads.title') }}
            </h1>
            @if ($canReceive)
                <p class="text-sm text-gray-500">
                    {{ __('provider.leads.count', ['count' => $leads->total()]) }}
                </p>
            @endif
        </div>

        @if (! $canReceive)
            <div class="p-8 rounded-2xl bg-amber-50 border border-amber-100 text-amber-800 text-center">
                <div class="text-3xl mb-2">⏳</div>
                <p class="text-sm">{{ __('provider.leads.empty_unverified') }}</p>
            </div>
        @else
            {{-- Filter bar --}}
            <form method="GET" action="{{ route('provider.leads.index') }}"
                  class="sticky top-0 z-10 bg-white/95 backdrop-blur border border-emerald-100/70 rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-5 gap-3">

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">{{ __('provider.leads.filter_service') }}</label>
                    <select name="service_type" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($serviceTypeOptions as $st)
                            <option value="{{ $st->value }}" @selected(($filters['service_type'] ?? '') === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">{{ __('provider.leads.filter_wilaya') }}</label>
                    <select name="wilaya_code" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($wilayaOptions as $w)
                            <option value="{{ $w->code }}" @selected(($filters['wilaya_code'] ?? '') === $w->code)>{{ $w->name_fr }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">{{ __('provider.leads.filter_budget') }}</label>
                    <select name="budget_range" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">{{ __('provider.leads.filter_all') }}</option>
                        @foreach ($budgetOptions as $b)
                            <option value="{{ $b->value }}" @selected(($filters['budget_range'] ?? '') === $b->value)>{{ $b->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs uppercase tracking-wider text-gray-500 mb-1">Tri</label>
                    <select name="sort" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="newest"       @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('provider.leads.sort_newest') }}</option>
                        <option value="oldest"       @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('provider.leads.sort_oldest') }}</option>
                        <option value="wedding_date" @selected(($filters['sort'] ?? '') === 'wedding_date')>{{ __('provider.leads.sort_wedding_date') }}</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">
                        Filtrer
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('provider.leads.index') }}" class="px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-brand">
                            ✕
                        </a>
                    @endif
                </div>
            </form>

            @if ($leads->isEmpty())
                <div class="p-10 rounded-2xl bg-white border border-emerald-100/70 shadow-sm text-center">
                    <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center text-3xl">📥</div>
                    @if (array_filter($filters))
                        <p class="mt-4 text-gray-700 font-medium">{{ __('provider.leads.empty_filters') }}</p>
                        <a href="{{ route('provider.leads.index') }}" class="mt-3 inline-block text-sm text-brand hover:underline">
                            {{ __('provider.leads.filter_reset') }}
                        </a>
                    @else
                        <h2 class="mt-4 font-display text-lg font-semibold text-gray-900">{{ __('provider.leads.empty_no_leads') }}</h2>
                        <p class="mt-2 text-sm text-gray-500">{{ __('provider.leads.empty_sub_default') }}</p>
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
