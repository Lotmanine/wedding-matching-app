@extends('layouts.provider')

@section('content')
    @php
        $wr = $wedRequest;
        $wilayaLabel = \App\Models\Wilaya::where('code', (string) $wr->couple_wilaya_code)->value('name_fr') ?? $wr->couple_wilaya_code;
        $whatsappDigits = preg_replace('/\D+/', '', (string) $wr->couple_whatsapp);
        $whatsappLink = config('zawaj.whatsapp.link_prefix') . $whatsappDigits;
        $phoneLink = 'tel:' . preg_replace('/\s+/', '', (string) $wr->couple_whatsapp);
        $serviceIcon = match ($lead->service_type?->value) {
            'photo' => '📷', 'video' => '🎥', 'traiteur' => '🍽️', 'decoration' => '🎨',
            'patisserie' => '🧁', 'salle' => '🏛️', 'musique' => '🎵', 'machta' => '💇',
            default => '📋',
        };
    @endphp

    <div class="max-w-5xl">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('provider.leads.index') }}" class="hover:text-brand">{{ __('provider.unlocked.back_to_leads') }}</a>
        </nav>

        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center gap-3 text-brand">
            <span class="text-2xl">✅</span>
            <div>
                <p class="font-semibold">{{ __('provider.unlocked.header') }}</p>
                <p class="text-xs text-brand/80">
                    {{ __('provider.unlocked.accepted_on', ['when' => $lead->unlocked_at?->diffForHumans() ?? '—']) }}
                    · {{ __('provider.unlocked.credits_charged', ['count' => $lead->credits_charged]) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main column --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-emerald-100/70 shadow-sm p-6 sm:p-8 space-y-6">
                <header class="flex items-center gap-3">
                    <span class="text-3xl sm:text-4xl">{{ $serviceIcon }}</span>
                    <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900">{{ $lead->service_type?->label() }}</h1>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Localisation</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wilayaLabel }}{{ $wr->couple_commune ? ' — ' . $wr->couple_commune : '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Date du mariage</p>
                        <p class="mt-1 font-medium text-gray-900">
                            {{ $wr->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr->wedding_season ?? '—') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Invités</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wr->guest_count?->label() ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Budget</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $wr->budget_range?->label() ?? '—' }}</p>
                    </div>
                </div>

                @if ($wr->description)
                    <div>
                        <p class="text-xs uppercase tracking-wider text-gray-500">Description</p>
                        <p class="mt-2 text-sm text-gray-700 italic leading-relaxed">« {{ $wr->description }} »</p>
                    </div>
                @endif

                <div class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-sm text-amber-900">
                    <p class="font-semibold">{{ __('provider.unlocked.tip_title') }}</p>
                    <p class="mt-1">{{ __('provider.unlocked.tip_body') }}</p>
                </div>
            </section>

            {{-- Contact card (now fully revealed) --}}
            <aside class="space-y-4">
                <div class="bg-white rounded-2xl border-2 border-brand shadow-sm p-5 space-y-4">
                    <h2 class="font-display text-lg font-semibold text-brand">{{ __('provider.unlocked.contact_title') }}</h2>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.unlocked.couple_name') }}</dt>
                            <dd class="mt-0.5 font-bold text-gray-900 text-base">{{ $wr->couple_first_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.unlocked.couple_wilaya') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $wilayaLabel }}</dd>
                        </div>
                        @if ($wr->couple_commune)
                            <div>
                                <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.unlocked.couple_commune') }}</dt>
                                <dd class="mt-0.5 font-medium text-gray-900">{{ $wr->couple_commune }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('provider.unlocked.couple_whatsapp') }}</dt>
                            <dd class="mt-0.5 font-mono font-semibold text-gray-900 select-all">{{ $wr->couple_whatsapp }}</dd>
                        </div>
                    </dl>

                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                       class="block w-full text-center py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md transition">
                        💬 {{ __('provider.unlocked.open_whatsapp') }}
                    </a>
                    <a href="{{ $phoneLink }}"
                       class="block w-full text-center py-3 rounded-xl border-2 border-emerald-200 hover:border-brand text-brand font-semibold text-sm transition">
                        📞 {{ __('provider.unlocked.call') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
@endsection
