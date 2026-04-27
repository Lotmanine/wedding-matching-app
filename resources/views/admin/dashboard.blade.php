@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ __('admin.dashboard.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('admin.dashboard.sub') }}</p>

        @include('admin.dashboard.partials.ops-heartbeat')

        {{-- Alerts --}}
        <div class="mt-6 space-y-2">
            @if ($stats['providers_pending'] > 0)
                <a href="{{ route('admin.providers.index', ['status' => 'pending']) }}" class="block p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm hover:bg-amber-100 transition">
                    ⏳ {{ __('admin.dashboard.alerts.pending_providers', ['count' => $stats['providers_pending']]) }} →
                </a>
            @endif
            @if ($stats['failed_purchases_24h'] > 0)
                <a href="{{ route('admin.purchases.index', ['status' => 'failed']) }}" class="block p-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm hover:bg-red-100 transition">
                    ⚠️ {{ __('admin.dashboard.alerts.failed_purchases', ['count' => $stats['failed_purchases_24h']]) }} →
                </a>
            @endif
            @if ($stats['purchases_pending_old'] > 0)
                <a href="{{ route('admin.purchases.index', ['status' => 'pending']) }}" class="block p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm hover:bg-amber-100 transition">
                    ⏸ {{ __('admin.dashboard.alerts.stuck_pending', ['count' => $stats['purchases_pending_old']]) }} →
                </a>
            @endif
            @if ($stats['stuck_matching'] > 0)
                <a href="{{ route('admin.service_requests.index', ['status' => 'matching']) }}" class="block p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm hover:bg-amber-100 transition">
                    🔄 {{ __('admin.dashboard.alerts.stuck_matching', ['count' => $stats['stuck_matching']]) }} →
                </a>
            @endif
        </div>

        {{-- KPIs --}}
        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @php
                $kpis = [
                    ['label' => __('admin.dashboard.kpi_wed_total'),   'value' => $stats['wed_requests_total']],
                    ['label' => __('admin.dashboard.kpi_wed_7d'),      'value' => $stats['wed_requests_7d']],
                    ['label' => __('admin.dashboard.kpi_wed_30d'),     'value' => $stats['wed_requests_30d']],
                    ['label' => __('admin.dashboard.kpi_couples'),     'value' => $stats['unique_couples']],
                    ['label' => __('admin.dashboard.kpi_providers'),   'value' => $stats['providers_total']],
                    ['label' => __('admin.dashboard.kpi_verified'),    'value' => $stats['providers_verified']],
                    ['label' => __('admin.dashboard.kpi_pending'),     'value' => $stats['providers_pending']],
                    ['label' => __('admin.dashboard.kpi_suspended'),   'value' => $stats['providers_suspended']],
                    ['label' => __('admin.dashboard.kpi_revenue'),     'value' => number_format($stats['revenue_dzd'], 0, ',', ' ')],
                    ['label' => __('admin.dashboard.kpi_credits_out'), 'value' => $stats['credits_outstanding']],
                    ['label' => __('admin.dashboard.kpi_match_rate'),  'value' => $stats['match_rate'] . '%'],
                    ['label' => __('admin.dashboard.kpi_avg_accept'),  'value' => $stats['avg_time_to_accept'] ?? '—'],
                ];
            @endphp
            @foreach ($kpis as $k)
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <p class="text-[11px] uppercase tracking-wider text-gray-500">{{ $k['label'] }}</p>
                    <p class="mt-1 font-display text-2xl font-bold text-ink">{{ $k['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- By-status breakdown --}}
        <section class="mt-8">
            <h2 class="font-display text-lg font-semibold text-gray-900 mb-3">Demandes de services par statut</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-9 gap-2">
                @foreach ($stats['service_requests_by_status'] as $status => $count)
                    <a href="{{ route('admin.service_requests.index', ['status' => $status]) }}" class="p-3 rounded-lg bg-white border border-gray-200 hover:border-brand transition">
                        <p class="text-[11px] text-gray-500 truncate">{{ $status }}</p>
                        <p class="font-display text-lg font-bold text-gray-900">{{ $count }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Recent activity --}}
        <section class="mt-8">
            <h2 class="font-display text-lg font-semibold text-gray-900 mb-3">{{ __('admin.dashboard.recent_activity') }}</h2>
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                @forelse ($recentActivity as $log)
                    <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
                        <div class="flex-1 min-w-0">
                            <span class="font-mono text-xs text-gray-500">{{ $log->action }}</span>
                            <span class="text-gray-700">par {{ $log->user?->email ?? 'système' }}</span>
                        </div>
                        <time class="text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at?->diffForHumans() }}</time>
                    </div>
                @empty
                    <p class="p-6 text-sm text-gray-500 text-center">Aucune activité.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
