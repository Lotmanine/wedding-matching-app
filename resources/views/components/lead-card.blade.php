@props(['lead'])

@php
    /** @var \App\Models\ServiceRequest $lead */
    $wr = $lead->wedRequest;
    $statusKey = 'status_' . str_replace('round', 'round_', (string) ($lead->status?->value ?? $lead->status));
    $statusBadge = match ($lead->status?->value ?? (string) $lead->status) {
        'round1'           => ['label' => __('provider.leads.status_round_1'), 'variant' => 'success'],
        'round2'           => ['label' => __('provider.leads.status_round_2'), 'variant' => 'warning'],
        'round3'           => ['label' => __('provider.leads.status_round_3'), 'variant' => 'accent'],
        'open_marketplace' => ['label' => __('provider.leads.status_open_marketplace'), 'variant' => 'blue'],
        default            => ['label' => ucfirst((string) ($lead->status?->value ?? $lead->status)), 'variant' => 'muted'],
    };

    $serviceIcon = match ($lead->service_type?->value) {
        'photo'      => 'camera_alt',
        'video'      => 'videocam',
        'catering'   => 'restaurant',
        'decoration' => 'local_florist',
        'pastry' => 'cake',
        'venue'      => 'villa',
        'music'    => 'music_note',
        'beauty'     => 'face_retouching_natural',
        default      => 'category',
    };

    $serviceLabel = $lead->service_type?->label() ?? (string) $lead->service_type;
    $cityLabel = \App\Models\City::where('code', (string) $wr->couple_city_code)->value('name') ?? $wr->couple_city_code;
    $locationLine = $cityLabel . ($wr->couple_district ? ' · ' . $wr->couple_district : '');

    $dateLine = $wr->wedding_date
        ? $wr->wedding_date->isoFormat('LL')
        : ($wr->wedding_season ? $wr->seasonLabel() . ' ' . $wr->created_at->year : __('Date flexible'));

    $guestLabel = $wr->guest_count?->label() ?? __('Non renseigné');
    $budgetLabel = $wr->budget_range?->label($wr->currency()) ?? __('Non renseigné');

    $initial = mb_strtoupper(mb_substr((string) $wr->couple_first_name, 0, 1)) . '.';
    $excerpt = $wr->description ? \Illuminate\Support\Str::limit($wr->description, 120) : '';
@endphp

<a href="{{ route('provider.leads.show', $lead->service_uid) }}"
   class="group block bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm hover:shadow-md hover:border-[#C8956C]/40 hover:-translate-y-0.5 transition-all duration-200 p-5 space-y-4 h-full">

    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-[#F0D9B5]/60 flex items-center justify-center shrink-0">
                <x-z.icon :name="$serviceIcon" class="text-[#C8956C] text-xl" />
            </div>
            <span class="font-serif text-lg font-bold text-[#1A1A1A]">{{ $serviceLabel }}</span>
        </div>
        <x-z.badge :variant="$statusBadge['variant']" class="whitespace-nowrap">{{ $statusBadge['label'] }}</x-z.badge>
    </div>

    <div class="space-y-2 text-sm text-[#6B665E]">
        <p class="flex items-center gap-2">
            <x-z.icon name="location_on" class="text-[#9A958D] text-base" /> {{ $locationLine }}
        </p>
        <p class="flex items-center gap-2">
            <x-z.icon name="calendar_month" class="text-[#9A958D] text-base" /> {{ $dateLine }}
        </p>
        <p class="flex items-center gap-2">
            <x-z.icon name="group" class="text-[#9A958D] text-base" /> {{ $guestLabel }}
        </p>
        <p class="flex items-center gap-2">
            <x-z.icon name="payments" class="text-[#9A958D] text-base" /> <span class="font-semibold text-[#1A1A1A]">{{ $budgetLabel }}</span>
        </p>
    </div>

    @if ($excerpt !== '')
        <p class="text-sm text-[#9A958D] italic border-t border-[#D4CFC7]/30 pt-3 leading-relaxed">« {{ $excerpt }} »</p>
    @endif

    <div class="flex items-center justify-between pt-3 border-t border-[#D4CFC7]/30 text-xs text-[#9A958D]">
        <span>{{ __('provider.leads.card_couple_initial', ['initial' => $initial, 'city' => $cityLabel]) }}</span>
        <span class="text-[#1A1A1A] font-bold flex items-center gap-1 group-hover:text-[#C8956C] transition-colors">{{ __('provider.leads.card_see_details') }} <x-z.icon name="arrow_forward" class="text-sm" /></span>
    </div>

    <p class="text-[11px] text-[#9A958D]">{{ __('provider.leads.card_time_ago', ['when' => $lead->created_at->diffForHumans()]) }}</p>
</a>
