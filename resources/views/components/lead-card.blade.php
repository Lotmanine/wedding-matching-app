@props(['lead'])

@php
    /** @var \App\Models\ServiceRequest $lead */
    $wr = $lead->wedRequest;
    $statusKey = 'status_' . str_replace('round', 'round_', (string) ($lead->status?->value ?? $lead->status));
    $statusBadge = match ($lead->status?->value ?? (string) $lead->status) {
        'round1'           => ['label' => '🟢 ' . __('provider.leads.status_round_1'), 'class' => 'bg-emerald-100 text-brand'],
        'round2'           => ['label' => '🟡 ' . __('provider.leads.status_round_2'), 'class' => 'bg-amber-100 text-amber-800'],
        'round3'           => ['label' => '🟠 ' . __('provider.leads.status_round_3'), 'class' => 'bg-orange-100 text-orange-800'],
        'open_marketplace' => ['label' => '🔓 ' . __('provider.leads.status_open_marketplace'), 'class' => 'bg-blue-100 text-blue-800'],
        default            => ['label' => '• ' . ucfirst((string) ($lead->status?->value ?? $lead->status)), 'class' => 'bg-gray-100 text-gray-700'],
    };

    $serviceIcon = match ($lead->service_type?->value) {
        'photo'      => '📷',
        'video'      => '🎥',
        'traiteur'   => '🍽️',
        'decoration' => '🎨',
        'patisserie' => '🧁',
        'salle'      => '🏛️',
        'musique'    => '🎵',
        'machta'     => '💇',
        default      => '📋',
    };

    $serviceLabel = $lead->service_type?->label() ?? (string) $lead->service_type;
    $wilayaLabel = \App\Models\Wilaya::where('code', (string) $wr->couple_wilaya_code)->value('name_fr') ?? $wr->couple_wilaya_code;
    $locationLine = $wilayaLabel . ($wr->couple_commune ? " — {$wr->couple_commune}" : '');

    $dateLine = $wr->wedding_date
        ? $wr->wedding_date->isoFormat('LL')
        : ($wr->wedding_season ? $wr->wedding_season . ' ' . $wr->created_at->year : '—');

    $guestLabel = $wr->guest_count?->label() ?? '—';
    $budgetLabel = $wr->budget_range?->label() ?? '—';

    $initial = mb_strtoupper(mb_substr((string) $wr->couple_first_name, 0, 1)) . '.';
    $excerpt = $wr->description ? \Illuminate\Support\Str::limit($wr->description, 120) : '';
@endphp

<a href="{{ route('provider.leads.show', $lead->service_uid) }}"
   class="block bg-white rounded-2xl border border-emerald-100/70 shadow-sm hover:shadow-md hover:border-brand transition p-5 space-y-3 h-full">

    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-2xl">{{ $serviceIcon }}</span>
            <span class="font-display text-lg font-bold text-gray-900">{{ $serviceLabel }}</span>
        </div>
        <span class="text-xs font-medium px-2.5 py-1 rounded-full whitespace-nowrap {{ $statusBadge['class'] }}">
            {{ $statusBadge['label'] }}
        </span>
    </div>

    <div class="space-y-1.5 text-sm text-gray-700">
        <p class="flex items-center gap-1.5">
            <span class="text-gray-400">📍</span> {{ $locationLine }}
        </p>
        <p class="flex items-center gap-1.5">
            <span class="text-gray-400">📅</span> {{ $dateLine }}
        </p>
        <p class="flex items-center gap-1.5">
            <span class="text-gray-400">👥</span> {{ $guestLabel }}
        </p>
        <p class="flex items-center gap-1.5">
            <span class="text-gray-400">💰</span> <span class="font-medium">{{ $budgetLabel }}</span>
        </p>
    </div>

    @if ($excerpt !== '')
        <p class="text-sm text-gray-500 italic border-t border-gray-100 pt-3">« {{ $excerpt }} »</p>
    @endif

    <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs text-gray-500">
        <span>{{ __('provider.leads.card_couple_initial', ['initial' => $initial, 'wilaya' => $wilayaLabel]) }}</span>
        <span class="text-brand font-medium hover:underline">{{ __('provider.leads.card_see_details') }} →</span>
    </div>

    <p class="text-[11px] text-gray-400">{{ __('provider.leads.card_time_ago', ['when' => $lead->created_at->diffForHumans()]) }}</p>
</a>
