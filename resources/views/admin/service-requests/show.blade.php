@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.service_requests.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> Services</a></nav>

        <div class="flex items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="font-serif text-xl font-bold text-[#1A1A1A]">{{ $sr->service_type?->label() }}</h1>
                <p class="text-xs font-mono text-[#9A958D] mt-0.5">{{ $sr->service_uid }} · parent <a href="{{ route('admin.requests.show', $sr->wedRequest?->wed_uid) }}" class="text-[#C8956C] hover:underline">{{ $sr->wedRequest?->wed_uid }}</a></p>
            </div>
            @if (in_array($sr->status?->value, ['new', 'matching', 'round1', 'round2', 'round3'], true))
                <form method="POST" action="{{ route('admin.service_requests.force_open', $sr->service_uid) }}">@csrf
                    <x-z.btn type="submit" variant="accent-outline" size="sm" icon="lock_open">{{ __('admin.service_requests.force_open_btn') }}</x-z.btn>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6 space-y-5">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Statut</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->status?->value ?? $sr->status }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Tour</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->current_round }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Tour démarré</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->round_started_at?->diffForHumans() ?? '—' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Expire</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->expires_at?->diffForHumans() }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Débloqué par</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->unlockedBy?->email ?? '—' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Via</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->unlocked_via ?? '—' }}</dd></div>
            </dl>

            @foreach (['round_1', 'round_2', 'round_3'] as $field)
                @php $ids = $sr->{$field . '_provider_ids'}; @endphp
                @if ($ids)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ str_replace('_', ' ', $field) }}</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach ((array) $ids as $pid)
                                <span class="text-[10px] bg-[#F7F4F0] text-[#6B665E] px-2 py-0.5 rounded-full font-mono border border-[#D4CFC7]/50">{{ $pid }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($sr->refused_by_provider_ids)
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#A84936] mb-1.5">Refusé par</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ((array) $sr->refused_by_provider_ids as $pid)
                            <span class="text-[10px] bg-red-50 text-[#A84936] px-2 py-0.5 rounded-full font-mono border border-red-100">{{ $pid }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @include('admin.service-requests.partials.whatsapp-delivery', ['sr' => $sr])
    </div>
@endsection
