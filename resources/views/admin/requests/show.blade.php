@extends('layouts.admin')

@section('content')
    @php $wr = $wedRequest; @endphp
    <div class="max-w-5xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.requests.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> Demandes</a></nav>

        <div class="flex items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ $wr->couple_first_name }}</h1>
                <p class="text-xs font-mono text-[#9A958D] mt-0.5">{{ $wr->wed_uid }}</p>
            </div>
            <div class="flex gap-2">
                @if (! $wr->deleted_at)
                    <x-z.btn :href="route('admin.requests.edit', $wr->wed_uid)" variant="outline" size="sm" icon="edit">Modifier</x-z.btn>
                @endif
                @if (! $wr->deleted_at && $wr->status?->value !== 'expired')
                    <form method="POST" action="{{ route('admin.requests.expire', $wr->wed_uid) }}">@csrf
                        <x-z.btn type="submit" variant="accent-outline" size="sm" icon="schedule">Expirer</x-z.btn>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-4">Contact couple</h2>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Prénom</dt><dd class="font-semibold text-[#1A1A1A] mt-0.5">{{ $wr->couple_first_name }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">WhatsApp</dt><dd class="font-mono text-[#1A1A1A] mt-0.5">{{ $wr->couple_whatsapp }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Wilaya</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->couple_city_code }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Commune</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->couple_district ?? 'Non renseigné' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Date</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->wedding_date?->isoFormat('LL') ?? $wr->seasonLabel() }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Invités</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->guest_count?->label() ?? 'Non renseigné' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Budget</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->budget_range?->label($wr->currency()) ?? 'Non renseigné' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Expire</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $wr->expires_at?->isoFormat('LL') }}</dd></div>
                    </dl>
                    @if ($wr->description)
                        <p class="mt-4 text-sm text-[#6B665E] border-t border-[#D4CFC7]/30 pt-4">{{ $wr->description }}</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-4">Services demandés</h2>
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-[#D4CFC7]/20">
                            @foreach ($wr->serviceRequests as $sr)
                                <tr>
                                    <td class="py-2.5 font-mono text-[#6B665E]">{{ $sr->service_uid }}</td>
                                    <td class="py-2.5 text-[#1A1A1A]">{{ $sr->service_type?->label() }}</td>
                                    <td class="py-2.5"><x-z.badge variant="default">{{ $sr->status?->value ?? $sr->status }}</x-z.badge></td>
                                    <td class="py-2.5 text-[#6B665E]">tour {{ $sr->current_round }}</td>
                                    <td class="py-2.5 text-right">
                                        @if ($sr->unlockedBy)
                                            débloqué par <a href="{{ route('admin.providers.show', $sr->unlockedBy->id) }}" class="text-[#C8956C] font-semibold hover:underline">{{ $sr->unlockedBy->email }}</a>
                                        @else
                                            <a href="{{ route('admin.service_requests.show', $sr->service_uid) }}" class="text-[#C8956C] font-semibold hover:underline">Voir</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <aside>
                @if (! $wr->deleted_at)
                    <div class="bg-white rounded-xl border border-red-100 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#A84936] mb-3"><x-z.icon name="delete_forever" class="text-lg" /> Suppression définitive</h3>
                        <form method="POST" action="{{ route('admin.requests.delete', $wr->wed_uid) }}" class="space-y-2.5">
                            @csrf
                            <input type="text" name="confirm" required placeholder="{{ __('admin.requests.delete_confirm_label') }}" class="w-full px-4 py-2.5 bg-white border border-red-200 rounded-lg text-sm outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15 transition-colors" />
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.requests.delete_reason_label') }}" class="w-full px-4 py-2.5 bg-white border border-red-200 rounded-lg text-sm outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15 transition-colors" />
                            <x-z.btn type="submit" variant="danger" size="sm" class="w-full">{{ __('admin.requests.delete_btn') }}</x-z.btn>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
