@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A]">{{ __('admin.dashboard.title') }}</h1>
            <p class="text-sm text-[#6B665E] mt-1">{{ __('admin.dashboard.sub') }}</p>
        </div>

        @include('admin.dashboard.partials.ops-heartbeat')

        {{-- Alerts --}}
        <div class="mt-6 space-y-2">
            @if ($stats['providers_pending'] > 0)
                <a href="{{ route('admin.providers.index', ['status' => 'pending']) }}" class="flex items-center gap-3 p-4 rounded-xl bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-[#7A5530] text-sm hover:bg-[#F0D9B5]/60 transition">
                    <x-z.icon name="pending_actions" :filled="true" class="text-xl text-[#C8956C]" />
                    <span class="flex-1">{{ trans_choice('admin.dashboard.alerts.pending_providers', $stats['providers_pending']) }}</span>
                    <x-z.icon name="arrow_forward" class="text-base" />
                </a>
            @endif
            @if ($stats['failed_purchases_24h'] > 0)
                <a href="{{ route('admin.purchases.index', ['status' => 'failed']) }}" class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm hover:bg-red-100/70 transition">
                    <x-z.icon name="warning" :filled="true" class="text-xl" />
                    <span class="flex-1">{{ trans_choice('admin.dashboard.alerts.failed_purchases', $stats['failed_purchases_24h']) }}</span>
                    <x-z.icon name="arrow_forward" class="text-base" />
                </a>
            @endif
            @if ($stats['purchases_pending_old'] > 0)
                <a href="{{ route('admin.purchases.index', ['status' => 'pending']) }}" class="flex items-center gap-3 p-4 rounded-xl bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-[#7A5530] text-sm hover:bg-[#F0D9B5]/60 transition">
                    <x-z.icon name="pause_circle" :filled="true" class="text-xl text-[#C8956C]" />
                    <span class="flex-1">{{ trans_choice('admin.dashboard.alerts.stuck_pending', $stats['purchases_pending_old']) }}</span>
                    <x-z.icon name="arrow_forward" class="text-base" />
                </a>
            @endif
            @if ($stats['stuck_matching'] > 0)
                <a href="{{ route('admin.service_requests.index', ['status' => 'matching']) }}" class="flex items-center gap-3 p-4 rounded-xl bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-[#7A5530] text-sm hover:bg-[#F0D9B5]/60 transition">
                    <x-z.icon name="sync_problem" :filled="true" class="text-xl text-[#C8956C]" />
                    <span class="flex-1">{{ trans_choice('admin.dashboard.alerts.stuck_matching', $stats['stuck_matching']) }}</span>
                    <x-z.icon name="arrow_forward" class="text-base" />
                </a>
            @endif
        </div>

        {{-- KPIs --}}
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                $kpis = [
                    ['label' => __('admin.dashboard.kpi_wed_total'),   'value' => $stats['wed_requests_total'],   'icon' => 'inbox'],
                    ['label' => __('admin.dashboard.kpi_wed_7d'),      'value' => $stats['wed_requests_7d'],      'icon' => 'calendar_view_week'],
                    ['label' => __('admin.dashboard.kpi_wed_30d'),     'value' => $stats['wed_requests_30d'],     'icon' => 'calendar_month'],
                    ['label' => __('admin.dashboard.kpi_couples'),     'value' => $stats['unique_couples'],       'icon' => 'favorite'],
                    ['label' => __('admin.dashboard.kpi_providers'),   'value' => $stats['providers_total'],      'icon' => 'storefront'],
                    ['label' => __('admin.dashboard.kpi_verified'),    'value' => $stats['providers_verified'],   'icon' => 'verified'],
                    ['label' => __('admin.dashboard.kpi_pending'),     'value' => $stats['providers_pending'],    'icon' => 'pending'],
                    ['label' => __('admin.dashboard.kpi_suspended'),   'value' => $stats['providers_suspended'],  'icon' => 'block'],
                    ['label' => __('admin.dashboard.kpi_revenue'),     'value' => \App\Support\Money::format($stats['revenue'], \App\Support\Money::currencyForCity(null)), 'icon' => 'payments'],
                    ['label' => __('admin.dashboard.kpi_credits_out'), 'value' => $stats['credits_outstanding'], 'icon' => 'toll'],
                    ['label' => __('admin.dashboard.kpi_match_rate'),  'value' => $stats['match_rate'] . '%',    'icon' => 'percent'],
                    ['label' => __('admin.dashboard.kpi_avg_accept'),  'value' => $stats['avg_time_to_accept'] ?? __('Not specified'), 'icon' => 'timer'],
                ];
            @endphp
            @foreach ($kpis as $k)
                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 flex items-start justify-between">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-2">{{ $k['label'] }}</p>
                        <p class="font-serif text-2xl sm:text-3xl font-bold text-[#1A1A1A] leading-none">{{ $k['value'] }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-[#F0D9B5] flex items-center justify-center shrink-0">
                        <x-z.icon :name="$k['icon']" :filled="true" class="text-xl text-[#C8956C]" />
                    </div>
                </div>
            @endforeach
        </div>

        {{-- By-status breakdown --}}
        <section class="mt-8">
            <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-3">{{ __('Service requests by status') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-9 gap-2">
                @foreach ($stats['service_requests_by_status'] as $status => $count)
                    <a href="{{ route('admin.service_requests.index', ['status' => $status]) }}" class="p-4 rounded-xl bg-white border border-[#D4CFC7]/50 shadow-sm hover:border-[#C8956C] hover:-translate-y-0.5 transition-all">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D] truncate">{{ $status }}</p>
                        <p class="font-serif text-xl font-bold text-[#1A1A1A] mt-1">{{ $count }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Recent activity --}}
        <section class="mt-8">
            <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-3">{{ __('admin.dashboard.recent_activity') }}</h2>
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden divide-y divide-[#D4CFC7]/20">
                @forelse ($recentActivity as $log)
                    <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm hover:bg-[#F7F4F0]/50 transition-colors">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-[#F7F4F0] flex items-center justify-center shrink-0">
                                <x-z.icon name="receipt_long" class="text-base text-[#C8956C]" />
                            </div>
                            <div class="min-w-0">
                                <span class="font-mono text-xs text-[#9A958D]">{{ $log->action }}</span>
                                <span class="text-[#1A1A1A]">{{ __('by :actor', ['actor' => $log->user?->email ?? __('system')]) }}</span>
                            </div>
                        </div>
                        <time class="text-xs text-[#9A958D] whitespace-nowrap">{{ $log->created_at?->diffForHumans() }}</time>
                    </div>
                @empty
                    <p class="p-6 text-sm text-[#9A958D] text-center">{{ __('No activity.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
