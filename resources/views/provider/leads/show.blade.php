@extends('layouts.provider')

@section('content')
    @php
        $wr = $wedRequest;
        $statusBadge = match ($lead->status?->value) {
            'round1'           => ['label' => '🟢 ' . __('provider.leads.status_round_1'), 'class' => 'bg-emerald-100 text-brand'],
            'round2'           => ['label' => '🟡 ' . __('provider.leads.status_round_2'), 'class' => 'bg-amber-100 text-amber-800'],
            'round3'           => ['label' => '🟠 ' . __('provider.leads.status_round_3'), 'class' => 'bg-orange-100 text-orange-800'],
            'open_marketplace' => ['label' => '🔓 ' . __('provider.leads.status_open_marketplace'), 'class' => 'bg-blue-100 text-blue-800'],
            default            => ['label' => '• ' . ucfirst((string) $lead->status?->value), 'class' => 'bg-gray-100 text-gray-700'],
        };
        $serviceIcon = match ($lead->service_type?->value) {
            'photo' => '📷', 'video' => '🎥', 'traiteur' => '🍽️', 'decoration' => '🎨',
            'patisserie' => '🧁', 'salle' => '🏛️', 'musique' => '🎵', 'machta' => '💇',
            default => '📋',
        };
        $wilayaLabel = \App\Models\Wilaya::where('code', (string) $wr->couple_wilaya_code)->value('name_fr') ?? $wr->couple_wilaya_code;
        $initial = mb_strtoupper(mb_substr((string) $wr->couple_first_name, 0, 1)) . '.';
        $dateLine = $wr->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr->wedding_season ? $wr->wedding_season . ' ' . $wr->created_at->year : '—');
        $durationLeft = $lead->expires_at ? $lead->expires_at->diffForHumans(now(), ['parts' => 2]) : '—';
    @endphp

    <div class="max-w-5xl">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('provider.leads.index') }}" class="hover:text-brand">{{ __('provider.leads.breadcrumb') }}</a>
            <span class="mx-2">›</span>
            <span class="text-gray-700 font-mono">{{ $lead->service_uid }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main column --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-emerald-100/70 shadow-sm p-6 sm:p-8 space-y-6">
                <header class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl sm:text-4xl">{{ $serviceIcon }}</span>
                        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ $lead->service_type?->label() }}</h1>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Localisation</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wilayaLabel }}{{ $wr->couple_commune ? ' — ' . $wr->couple_commune : '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Date du mariage</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $dateLine }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Invités</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wr->guest_count?->label() ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Budget</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wr->budget_range?->label() ?? '—' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.all_services') }}</p>
                        <div class="mt-1 flex flex-wrap gap-1.5">
                            @foreach ($wr->serviceRequests as $sibling)
                                <span @class([
                                    'px-2.5 py-1 rounded-full text-xs font-medium',
                                    'bg-brand text-white' => $sibling->id === $lead->id,
                                    'bg-emerald-50 text-brand' => $sibling->id !== $lead->id,
                                ])>
                                    {{ $sibling->service_type?->label() }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.duration_left') }}</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $durationLeft }}</p>
                    </div>
                </div>

                @if ($wr->description)
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.description') }}</p>
                        <p class="mt-2 text-sm text-gray-700 italic leading-relaxed">« {{ $wr->description }} »</p>
                    </div>
                @endif

                <div class="text-xs text-gray-400 border-t border-gray-100 pt-4">
                    {{ __('provider.leads.created_on', ['date' => $lead->created_at->isoFormat('LL')]) }} ·
                    {{ __('provider.leads.expires_on', ['date' => $lead->expires_at?->isoFormat('LL') ?? '—']) }}
                </div>
            </section>

            {{-- Right sidebar — couple card (redacted) --}}
            <aside class="space-y-4">
                <div class="bg-white rounded-2xl border border-emerald-100/70 shadow-sm p-5 space-y-4">
                    <h2 class="font-display text-lg font-semibold text-brand">{{ __('provider.leads.couple_card') }}</h2>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.couple_first') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $initial }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.couple_wilaya') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $wilayaLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.leads.couple_whatsapp') }}</dt>
                            <dd class="mt-0.5 font-mono text-gray-500 select-none" aria-hidden="true">
                                🔒 <span style="filter: blur(4px);">+213 5XX XXX XXX</span>
                            </dd>
                        </div>
                    </dl>

                    <p class="text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg p-3 leading-relaxed">
                        {{ __('provider.leads.reveal_notice') }}
                    </p>
                </div>

                <div x-data="{ showAccept: false, showRefuse: false }" class="space-y-2">
                    @if (session('error'))
                        <div class="p-3 rounded-lg bg-red-50 border border-red-100 text-red-700 text-xs">{{ session('error') }}</div>
                    @endif

                    @php $cost = (int) config('zawaj.credits.unlock_cost', 1); @endphp

                    <button type="button" @click="showAccept = true"
                            class="w-full py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition">
                        ✓ {{ __('provider.leads.btn_accept') }} ({{ $cost }} crédit{{ $cost > 1 ? 's' : '' }})
                    </button>
                    <button type="button" @click="showRefuse = true"
                            class="w-full py-3 rounded-xl border-2 border-gray-200 hover:border-red-300 hover:text-red-600 text-gray-600 font-semibold text-sm transition">
                        ✕ {{ __('provider.leads.btn_refuse') }}
                    </button>

                    <div x-show="showAccept" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                         @keydown.escape.window="showAccept = false">
                        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4" @click.outside="showAccept = false">
                            <h3 class="font-display text-lg font-semibold text-gray-900">{{ __('provider.acceptance.confirm_title') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('provider.acceptance.confirm_body', ['cost' => $cost]) }}</p>
                            <form method="POST" action="{{ route('provider.leads.accept', $lead->service_uid) }}" class="flex gap-2">
                                @csrf
                                <button type="button" @click="showAccept = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                    {{ __('provider.acceptance.cancel_btn') }}
                                </button>
                                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">
                                    {{ __('provider.acceptance.confirm_btn') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div x-show="showRefuse" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                         @keydown.escape.window="showRefuse = false">
                        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 space-y-4" @click.outside="showRefuse = false">
                            <h3 class="font-display text-lg font-semibold text-gray-900">{{ __('provider.refusal.confirm_title') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('provider.refusal.confirm_body') }}</p>
                            <form method="POST" action="{{ route('provider.leads.refuse', $lead->service_uid) }}" class="flex gap-2">
                                @csrf
                                <button type="button" @click="showRefuse = false" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                                    {{ __('provider.acceptance.cancel_btn') }}
                                </button>
                                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">
                                    {{ __('provider.refusal.confirm_btn') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </div>
@endsection
