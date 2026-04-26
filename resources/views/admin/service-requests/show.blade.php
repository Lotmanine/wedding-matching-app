@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.service_requests.index') }}" class="hover:text-brand">← Services</a></nav>

        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h1 class="font-display text-xl font-bold">{{ $sr->service_type?->label() }}</h1>
                <p class="text-xs font-mono text-gray-500">{{ $sr->service_uid }} · parent <a href="{{ route('admin.requests.show', $sr->wedRequest?->wed_uid) }}" class="text-brand">{{ $sr->wedRequest?->wed_uid }}</a></p>
            </div>
            @if (in_array($sr->status?->value, ['new', 'matching', 'round1', 'round2', 'round3'], true))
                <form method="POST" action="{{ route('admin.service_requests.force_open', $sr->service_uid) }}">@csrf
                    <button class="px-3 py-1.5 rounded-lg border-2 border-amber-300 text-amber-800 hover:bg-amber-50 text-sm font-semibold transition">{{ __('admin.service_requests.force_open_btn') }}</button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-gray-500 text-xs uppercase">Statut</dt><dd>{{ $sr->status?->value ?? $sr->status }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase">Tour</dt><dd>{{ $sr->current_round }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase">Tour démarré</dt><dd>{{ $sr->round_started_at?->diffForHumans() ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase">Expire</dt><dd>{{ $sr->expires_at?->diffForHumans() }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase">Débloqué par</dt><dd>{{ $sr->unlockedBy?->email ?? '—' }}</dd></div>
                <div><dt class="text-gray-500 text-xs uppercase">Via</dt><dd>{{ $sr->unlocked_via ?? '—' }}</dd></div>
            </dl>

            @foreach (['round_1', 'round_2', 'round_3'] as $field)
                @php $ids = $sr->{$field . '_provider_ids'}; @endphp
                @if ($ids)
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-1">{{ str_replace('_', ' ', $field) }}</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach ((array) $ids as $pid)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 font-mono">{{ $pid }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($sr->refused_by_provider_ids)
                <div>
                    <p class="text-xs uppercase tracking-wider text-red-500 mb-1">Refusé par</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ((array) $sr->refused_by_provider_ids as $pid)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-mono">{{ $pid }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @include('admin.service-requests.partials.whatsapp-delivery', ['sr' => $sr])
    </div>
@endsection
