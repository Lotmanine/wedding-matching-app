@extends('layouts.provider')

@section('content')
    @php
        $wr = $wedRequest;
        $statusBadge = match ($lead->status?->value) {
            'round1'           => ['label' => __('provider.leads.status_round_1'), 'variant' => 'success'],
            'round2'           => ['label' => __('provider.leads.status_round_2'), 'variant' => 'warning'],
            'round3'           => ['label' => __('provider.leads.status_round_3'), 'variant' => 'accent'],
            'open_marketplace' => ['label' => __('provider.leads.status_open_marketplace'), 'variant' => 'blue'],
            default            => ['label' => ucfirst((string) $lead->status?->value), 'variant' => 'muted'],
        };
        $serviceIcon = match ($lead->service_type?->value) {
            'photo' => 'camera_alt', 'video' => 'videocam', 'traiteur' => 'restaurant', 'decoration' => 'local_florist',
            'patisserie' => 'cake', 'salle' => 'villa', 'musique' => 'music_note', 'machta' => 'face_retouching_natural',
            default => 'category',
        };
        $wilayaLabel = \App\Models\Wilaya::where('code', (string) $wr->couple_wilaya_code)->value('name_fr') ?? $wr->couple_wilaya_code;
        $initial = mb_strtoupper(mb_substr((string) $wr->couple_first_name, 0, 1)) . '.';
        $dateLine = $wr->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr->wedding_season ? $wr->wedding_season . ' ' . $wr->created_at->year : '—');
        $durationLeft = $lead->expires_at ? $lead->expires_at->diffForHumans(now(), ['parts' => 2]) : '—';
        $cost = (int) config('zawaj.credits.unlock_cost', 1);
        $balance = (int) (auth()->user()?->providerProfile?->credit_balance ?? 0);
    @endphp

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-8">
            <a href="{{ route('provider.leads.index') }}" class="flex items-center gap-2 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors font-medium">
                <x-z.icon name="arrow_back" class="text-lg" /> {{ __('provider.leads.breadcrumb') }}
            </a>
            <x-z.badge :variant="$statusBadge['variant']">{{ $statusBadge['label'] }}</x-z.badge>
            <span class="text-xs text-[#9A958D] font-mono ml-auto">{{ $lead->service_uid }}</span>
        </div>

        {{-- Teaser header --}}
        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8 mb-6">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-[#F0D9B5]/60 flex items-center justify-center shrink-0">
                            <x-z.icon :name="$serviceIcon" class="text-[#C8956C] text-2xl" />
                        </div>
                        <h1 class="font-serif text-2xl md:text-3xl font-bold text-[#1A1A1A]">{{ $lead->service_type?->label() }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                            <x-z.icon name="location_on" class="text-[#C8956C] text-base" />{{ $wilayaLabel }}{{ $wr->couple_commune ? ' — ' . $wr->couple_commune : '' }}
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                            <x-z.icon name="calendar_month" class="text-[#C8956C] text-base" />{{ $dateLine }}
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                            <x-z.icon name="group" class="text-[#C8956C] text-base" />{{ $wr->guest_count?->label() ?? '—' }}
                        </div>
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                            <x-z.icon name="payments" class="text-[#C8956C] text-base" />{{ $wr->budget_range?->label() ?? '—' }}
                        </div>
                    </div>
                </div>
                <div class="shrink-0">
                    <div class="bg-[#F7F4F0] rounded-xl px-5 py-4 text-center border border-[#D4CFC7]/50">
                        <p class="text-xs text-[#9A958D] mb-1">{{ __('provider.leads.duration_left') }}</p>
                        <p class="text-lg font-serif font-bold text-[#1A1A1A]">{{ $durationLeft }}</p>
                    </div>
                </div>
            </div>

            {{-- All services + meta --}}
            <div class="mt-6 pt-6 border-t border-[#D4CFC7]/30 space-y-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">{{ __('provider.leads.all_services') }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($wr->serviceRequests as $sibling)
                            <span @class([
                                'px-3 py-1.5 rounded-full text-xs font-semibold',
                                'bg-[#1A1A1A] text-white' => $sibling->id === $lead->id,
                                'bg-[#F7F4F0] text-[#6B665E] border border-[#D4CFC7]/50' => $sibling->id !== $lead->id,
                            ])>
                                {{ $sibling->service_type?->label() }}
                            </span>
                        @endforeach
                    </div>
                </div>

                @if ($wr->description)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">{{ __('provider.leads.description') }}</p>
                        <p class="text-sm text-[#1A1A1A] italic leading-relaxed">« {{ $wr->description }} »</p>
                    </div>
                @endif

                <p class="text-xs text-[#9A958D] pt-2">
                    {{ __('provider.leads.created_on', ['date' => $lead->created_at->isoFormat('LL')]) }} ·
                    {{ __('provider.leads.expires_on', ['date' => $lead->expires_at?->isoFormat('LL') ?? '—']) }}
                </p>
            </div>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm flex items-center gap-3">
                <x-z.icon name="error" class="text-xl shrink-0" />{{ session('error') }}
            </div>
        @endif

        {{-- Locked contact reveal --}}
        <div x-data="{ showAccept: false, showRefuse: false }">
            <div class="relative bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden" style="min-height: 320px;">
                {{-- Blurred content behind --}}
                <div class="filter blur-[3px] select-none p-6 md:p-8" aria-hidden="true">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-4">{{ __('provider.leads.couple_card') }}</p>
                    <div class="p-4 bg-[#F7F4F0] rounded-xl mb-5">
                        <p class="font-bold text-[#1A1A1A]">{{ $initial }} & ██████</p>
                        <p class="text-[#6B665E] font-mono">+213 6 ██ ██ ██ ██</p>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-3">{{ __('provider.leads.couple_wilaya') }}</p>
                    <p class="text-[#1A1A1A]">{{ $wilayaLabel }}</p>
                </div>

                {{-- Centered overlay --}}
                <div class="absolute inset-0 backdrop-blur-[2px] bg-white/70 flex flex-col items-center justify-center p-6 text-center">
                    <div class="w-16 h-16 bg-[#F0D9B5] rounded-2xl flex items-center justify-center mb-5 shadow-lg shadow-[#C8956C]/10">
                        <x-z.icon name="lock" :filled="true" class="text-[#C8956C] text-3xl" />
                    </div>
                    <h3 class="font-serif text-xl font-bold text-[#1A1A1A] mb-2">{{ __('provider.leads.couple_card') }}</h3>
                    <p class="text-sm text-[#6B665E] mb-7 max-w-sm leading-relaxed">{{ __('provider.leads.reveal_notice') }}</p>
                    <div class="flex flex-col sm:flex-row gap-2 w-full max-w-md">
                        <button type="button" @click="showAccept = true"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-[#C8956C] text-white font-semibold text-base hover:bg-[#b8845b] shadow-sm hover:shadow-md transition-all active:scale-[0.97]">
                            <x-z.icon name="lock_open" class="text-lg" /> {{ __('provider.leads.btn_accept') }} ({{ $cost }} {{ $cost > 1 ? 'crédits' : 'crédit' }})
                        </button>
                        <button type="button" @click="showRefuse = true"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-lg bg-white border border-[#D4CFC7] text-[#6B665E] font-semibold text-sm hover:border-[#A84936]/40 hover:text-[#A84936] transition-all">
                            <x-z.icon name="visibility_off" class="text-lg" /> {{ __('provider.leads.btn_refuse') }}
                        </button>
                    </div>
                    <p class="text-xs text-[#9A958D] mt-3">{{ __('credits.balance_label') }} : {{ $balance }} {{ __('provider.nav.credits_available') }}</p>
                </div>
            </div>

            {{-- Accept confirmation modal --}}
            <div x-show="showAccept" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @keydown.escape.window="showAccept = false">
                <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4" @click.outside="showAccept = false">
                    <h3 class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('provider.acceptance.confirm_title') }}</h3>
                    <p class="text-sm text-[#6B665E]">{{ __('provider.acceptance.confirm_body', ['cost' => $cost]) }}</p>
                    <form method="POST" action="{{ route('provider.leads.accept', $lead->service_uid) }}" class="flex gap-2">
                        @csrf
                        <x-z.btn type="button" variant="outline" size="md" @click="showAccept = false" class="flex-1 justify-center">
                            {{ __('provider.acceptance.cancel_btn') }}
                        </x-z.btn>
                        <x-z.btn type="submit" variant="accent" size="md" class="flex-1 justify-center">
                            {{ __('provider.acceptance.confirm_btn') }}
                        </x-z.btn>
                    </form>
                </div>
            </div>

            {{-- Refuse confirmation modal --}}
            <div x-show="showRefuse" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                 @keydown.escape.window="showRefuse = false">
                <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4" @click.outside="showRefuse = false">
                    <h3 class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('provider.refusal.confirm_title') }}</h3>
                    <p class="text-sm text-[#6B665E]">{{ __('provider.refusal.confirm_body') }}</p>
                    <form method="POST" action="{{ route('provider.leads.refuse', $lead->service_uid) }}" class="flex gap-2">
                        @csrf
                        <x-z.btn type="button" variant="outline" size="md" @click="showRefuse = false" class="flex-1 justify-center">
                            {{ __('provider.acceptance.cancel_btn') }}
                        </x-z.btn>
                        <x-z.btn type="submit" variant="danger" size="md" class="flex-1 justify-center">
                            {{ __('provider.refusal.confirm_btn') }}
                        </x-z.btn>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
