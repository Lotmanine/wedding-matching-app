@extends('layouts.provider')

@section('content')
    @php
        $wr = $wedRequest;
        $wilayaLabel = \App\Models\Wilaya::where('code', (string) $wr->couple_wilaya_code)->value('name_fr') ?? $wr->couple_wilaya_code;
        $whatsappDigits = preg_replace('/\D+/', '', (string) $wr->couple_whatsapp);
        $whatsappLink = config('zawaj.whatsapp.link_prefix') . $whatsappDigits;
        $phoneLink = 'tel:' . preg_replace('/\s+/', '', (string) $wr->couple_whatsapp);
        $anonymized = $lead->isAnonymized();
        $anonymizeAt = $lead->anonymizeAt();
        $serviceIcon = match ($lead->service_type?->value) {
            'photo' => 'camera_alt', 'video' => 'videocam', 'traiteur' => 'restaurant', 'decoration' => 'local_florist',
            'patisserie' => 'cake', 'salle' => 'villa', 'musique' => 'music_note', 'machta' => 'face_retouching_natural',
            default => 'category',
        };
    @endphp

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('provider.leads.index') }}" class="flex items-center gap-2 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors font-medium">
                <x-z.icon name="arrow_back" class="text-lg" /> {{ __('provider.unlocked.back_to_leads') }}
            </a>
        </div>

        {{-- Success banner --}}
        <div class="mb-3 bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
            <x-z.icon name="check_circle" :filled="true" class="text-green-600 text-2xl shrink-0" />
            <div>
                <p class="text-sm font-bold text-green-800">{{ __('provider.unlocked.header') }}</p>
                <p class="text-xs text-green-600">
                    {{ __('provider.unlocked.accepted_on', ['when' => $lead->unlocked_at?->diffForHumans() ?? 'Non renseigné']) }}
                    · {{ __('provider.unlocked.credits_charged', ['count' => $lead->credits_charged]) }}
                </p>
            </div>
        </div>

        {{-- ANPDP anonymization notice --}}
        @if ($anonymized)
            <div class="mb-6 bg-[#F7F4F0] border border-[#D4CFC7]/60 rounded-xl p-4 flex items-center gap-3">
                <x-z.icon name="visibility_off" class="text-[#9A958D] text-2xl shrink-0" />
                <div>
                    <p class="text-sm font-bold text-[#1A1A1A]">Coordonnées anonymisées</p>
                    <p class="text-xs text-[#6B665E]">Conformité ANPDP : les données de contact du couple ne sont plus affichées ({{ (int) config('zawaj.anpdp.retention_days', 14) }} jours après l'acceptation).</p>
                </div>
            </div>
        @elseif ($anonymizeAt)
            <div class="mb-6 bg-[#F0D9B5]/30 border border-[#C8956C]/30 rounded-xl p-4 flex items-center gap-3">
                <x-z.icon name="schedule" class="text-[#C8956C] text-2xl shrink-0" />
                <div>
                    <p class="text-sm font-bold text-[#1A1A1A]">Cette demande sera anonymisée {{ $anonymizeAt->diffForHumans() }}</p>
                    <p class="text-xs text-[#6B665E]">Le {{ $anonymizeAt->isoFormat('LL') }}, les coordonnées du couple seront masquées (conformité ANPDP). Notez les informations dont vous avez besoin.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main column --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8 space-y-6">
                <header class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#F0D9B5]/60 flex items-center justify-center shrink-0">
                        <x-z.icon :name="$serviceIcon" class="text-[#C8956C] text-2xl" />
                    </div>
                    <h1 class="font-serif text-2xl md:text-3xl font-bold text-[#1A1A1A]">{{ $lead->service_type?->label() }}</h1>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="location_on" class="text-[#C8956C] text-base shrink-0" />{{ $wilayaLabel }}{{ $wr->couple_commune ? ' · ' . $wr->couple_commune : '' }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="calendar_month" class="text-[#C8956C] text-base shrink-0" />{{ $wr->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr->wedding_season ?? 'Non renseigné') }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="group" class="text-[#C8956C] text-base shrink-0" />{{ $wr->guest_count?->label() ?? 'Non renseigné' }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="payments" class="text-[#C8956C] text-base shrink-0" />{{ $wr->budget_range?->label() ?? 'Non renseigné' }}
                    </div>
                </div>

                @if ($wr->description)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">Description</p>
                        <p class="text-[#1A1A1A] italic leading-relaxed">« {{ $wr->description }} »</p>
                    </div>
                @endif

                <div class="p-4 rounded-xl bg-[#F0D9B5]/30 border border-[#C8956C]/20 text-sm">
                    <p class="font-bold text-[#1A1A1A] flex items-center gap-2"><x-z.icon name="lightbulb" :filled="true" class="text-[#C8956C] text-lg" />{{ __('provider.unlocked.tip_title') }}</p>
                    <p class="mt-1 text-[#6B665E] leading-relaxed">{{ __('provider.unlocked.tip_body') }}</p>
                </div>
            </section>

            {{-- Contact card (revealed) --}}
            <aside class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#C8956C]/30 shadow-sm p-6 space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('provider.unlocked.contact_title') }}</p>

                    <div class="space-y-3">
                        <div class="p-4 bg-[#F0D9B5]/30 rounded-xl border border-[#C8956C]/20">
                            <p class="text-xs text-[#9A958D] mb-1">{{ __('provider.unlocked.couple_name') }}</p>
                            <p class="font-bold text-[#1A1A1A] text-base">{{ $anonymized ? 'Anonymisé' : $wr->couple_first_name }}</p>
                        </div>
                        <div class="flex items-start gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm">
                            <x-z.icon name="location_on" class="text-[#C8956C] text-base shrink-0" />
                            <div>
                                <span class="text-xs text-[#9A958D] block">{{ __('provider.unlocked.couple_wilaya') }}</span>
                                <span class="font-medium text-[#1A1A1A]">{{ $wilayaLabel }}</span>
                            </div>
                        </div>
                        @if ($wr->couple_commune)
                            <div class="flex items-start gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm">
                                <x-z.icon name="pin_drop" class="text-[#C8956C] text-base shrink-0" />
                                <div>
                                    <span class="text-xs text-[#9A958D] block">{{ __('provider.unlocked.couple_commune') }}</span>
                                    <span class="font-medium text-[#1A1A1A]">{{ $wr->couple_commune }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="p-4 bg-[#25D366]/10 rounded-xl border border-[#25D366]/30">
                            <p class="text-xs text-[#9A958D] mb-1">{{ __('provider.unlocked.couple_whatsapp') }}</p>
                            <p class="font-mono font-bold text-[#1A1A1A] select-all">{{ $anonymized ? 'Anonymisé' : $wr->couple_whatsapp }}</p>
                        </div>
                    </div>

                    @unless ($anonymized)
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-lg bg-[#25D366] text-white font-semibold text-sm hover:bg-[#1ebe5d] shadow-sm hover:shadow-md transition-all active:scale-[0.97]">
                            <x-z.icon name="chat" :filled="true" class="text-lg" /> {{ __('provider.unlocked.open_whatsapp') }}
                        </a>
                        <a href="{{ $phoneLink }}"
                           class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-lg bg-white border border-[#D4CFC7] text-[#1A1A1A] font-semibold text-sm hover:bg-[#F7F4F0] hover:border-[#C8956C] transition-all">
                            <x-z.icon name="call" class="text-lg" /> {{ __('provider.unlocked.call') }}
                        </a>
                    @endunless
                </div>

                {{-- Litige / crédit-back : signaler une demande morte --}}
                <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-3">Un problème avec cette demande ?</p>

                    @if (($dispute ?? null) !== null)
                        <div @class([
                            'p-3 rounded-lg text-sm border',
                            'bg-[#F0D9B5]/30 border-[#C8956C]/30 text-[#7A5530]' => $dispute->status === 'pending',
                            'bg-green-50 border-green-200 text-green-800'        => $dispute->status === 'approved',
                            'bg-red-50 border-red-100 text-[#A84936]'            => $dispute->status === 'rejected',
                        ])>
                            @if ($dispute->status === 'pending')
                                <p class="font-semibold">Signalement en cours d'examen</p>
                                <p class="text-xs mt-0.5">Motif : {{ $dispute->reasonLabel() }} · envoyé {{ $dispute->created_at->diffForHumans() }}</p>
                            @elseif ($dispute->status === 'approved')
                                <p class="font-semibold">Signalement accepté : crédits remboursés</p>
                                <p class="text-xs mt-0.5">{{ $dispute->decided_at?->diffForHumans() }}</p>
                            @else
                                <p class="font-semibold">Signalement refusé</p>
                                @if ($dispute->admin_note)<p class="text-xs mt-0.5">{{ $dispute->admin_note }}</p>@endif
                            @endif
                        </div>
                    @else
                        <details class="group">
                            <summary class="cursor-pointer text-sm font-semibold text-[#6B665E] hover:text-[#A84936] transition-colors list-none flex items-center gap-1.5">
                                <x-z.icon name="flag" class="text-base" /> Signaler cette demande
                            </summary>
                            <form method="POST" action="{{ route('provider.leads.dispute', $lead->service_uid) }}" class="mt-3 space-y-2.5">
                                @csrf
                                <select name="reason" required class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15">
                                    <option value="">Motif du signalement…</option>
                                    <option value="wrong_number">Numéro erroné / injoignable</option>
                                    <option value="no_response">Aucune réponse du couple</option>
                                    <option value="fake">Demande factice / spam</option>
                                    <option value="other">Autre</option>
                                </select>
                                <textarea name="details" rows="2" maxlength="500" placeholder="Précisions (facultatif)"
                                          class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 resize-none"></textarea>
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-[#A84936]/40 text-[#A84936] font-semibold text-sm hover:bg-red-50 transition-colors">
                                    Envoyer le signalement
                                </button>
                                <p class="text-[11px] text-[#9A958D]">Si le problème est confirmé par notre équipe, vos crédits vous sont remboursés.</p>
                            </form>
                        </details>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection
