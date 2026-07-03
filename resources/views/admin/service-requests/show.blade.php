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
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Tour démarré</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->round_started_at?->diffForHumans() ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Expire</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->expires_at?->diffForHumans() }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Débloqué par</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->unlockedBy?->email ?? 'Non renseigné' }}</dd></div>
                <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Via</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $sr->unlocked_via ?? 'Non renseigné' }}</dd></div>
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

        {{-- ── Admin manual controls (manual round-robin) ─────────── --}}
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Assign to a verified provider (silent) --}}
            <div class="lg:col-span-1 bg-white rounded-xl border border-[#C8956C]/30 shadow-sm p-5">
                <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#7A5530] mb-3"><x-z.icon name="assignment_ind" class="text-lg text-[#C8956C]" /> Assigner un prestataire</h3>
                @if ($sr->unlocked_by_user_id)
                    <p class="text-xs text-[#6B665E]">Déjà accepté : assignation impossible.</p>
                @elseif ($verifiedProviders->isEmpty())
                    <p class="text-xs text-[#6B665E]">Aucun prestataire vérifié pour ce service.</p>
                @else
                    <form method="POST" action="{{ route('admin.service_requests.assign', $sr->service_uid) }}" class="space-y-3">
                        @csrf
                        <select name="provider_id" required class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15">
                            <option value="">Choisir un prestataire…</option>
                            @foreach ($verifiedProviders as $p)
                                <option value="{{ $p->id }}">{{ $p->providerProfile?->business_name ?? $p->name }} · {{ $p->providerProfile?->credit_balance ?? 0 }} cr</option>
                            @endforeach
                        </select>
                        <select name="round" class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15">
                            <option value="">Tour actuel</option>
                            <option value="1">Tour 1</option>
                            <option value="2">Tour 2</option>
                            <option value="3">Tour 3</option>
                        </select>
                        <x-z.btn type="submit" variant="accent" size="sm" class="w-full justify-center">Assigner (sans notification)</x-z.btn>
                        <p class="text-[11px] text-[#9A958D]">Le prestataire devient éligible à la demande. Aucune notification automatique n'est envoyée.</p>
                    </form>
                @endif
            </div>

            {{-- Change status --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#1A1A1A] mb-3"><x-z.icon name="tune" class="text-lg text-[#C8956C]" /> Changer le statut</h3>
                <form method="POST" action="{{ route('admin.service_requests.status', $sr->service_uid) }}" class="space-y-3">
                    @csrf
                    <select name="status" required class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15">
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" @selected(($sr->status?->value ?? $sr->status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <x-z.btn type="submit" variant="outline" size="sm" class="w-full justify-center">Appliquer</x-z.btn>
                </form>
            </div>

            {{-- Change expiry --}}
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#1A1A1A] mb-3"><x-z.icon name="schedule" class="text-lg text-[#C8956C]" /> Date d'expiration</h3>
                <form method="POST" action="{{ route('admin.service_requests.expiry', $sr->service_uid) }}" class="space-y-3">
                    @csrf
                    <input type="datetime-local" name="expires_at"
                        value="{{ $sr->expires_at?->format('Y-m-d\TH:i') }}"
                        class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
                    <x-z.btn type="submit" variant="outline" size="sm" class="w-full justify-center">Enregistrer</x-z.btn>
                    <p class="text-[11px] text-[#9A958D]">Laissez vide pour retirer l'expiration.</p>
                </form>
            </div>
        </div>

        @include('admin.service-requests.partials.whatsapp-delivery', ['sr' => $sr])
    </div>
@endsection
