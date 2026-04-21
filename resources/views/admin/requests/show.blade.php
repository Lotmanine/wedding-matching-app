@extends('layouts.admin')

@section('content')
    @php $wr = $wedRequest; @endphp
    <div class="max-w-5xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.requests.index') }}" class="hover:text-brand">← Demandes</a></nav>

        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h1 class="font-display text-2xl font-bold">{{ $wr->couple_first_name }}</h1>
                <p class="text-xs font-mono text-gray-500">{{ $wr->wed_uid }}</p>
            </div>
            <div class="flex gap-2">
                @if (! $wr->deleted_at && $wr->status?->value !== 'expired')
                    <form method="POST" action="{{ route('admin.requests.expire', $wr->wed_uid) }}">@csrf
                        <button class="px-3 py-1.5 rounded-lg border border-amber-300 text-amber-800 hover:bg-amber-50 text-sm transition">Expirer</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="lg:col-span-2 space-y-5">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-display text-lg font-semibold mb-3">Contact couple</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-gray-500 text-xs uppercase">Prénom</dt><dd class="font-semibold">{{ $wr->couple_first_name }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">WhatsApp</dt><dd class="font-mono">{{ $wr->couple_whatsapp }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Wilaya</dt><dd>{{ $wr->couple_wilaya_code }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Commune</dt><dd>{{ $wr->couple_commune ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Date</dt><dd>{{ $wr->wedding_date?->isoFormat('LL') ?? $wr->wedding_season }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Invités</dt><dd>{{ $wr->guest_count?->label() ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Budget</dt><dd>{{ $wr->budget_range?->label() ?? '—' }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Expire</dt><dd>{{ $wr->expires_at?->isoFormat('LL') }}</dd></div>
                    </dl>
                    @if ($wr->description)
                        <p class="mt-3 text-sm text-gray-600 border-t border-gray-100 pt-3">{{ $wr->description }}</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-display text-lg font-semibold mb-3">Services demandés</h2>
                    <table class="w-full text-xs">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($wr->serviceRequests as $sr)
                                <tr>
                                    <td class="py-2 font-mono">{{ $sr->service_uid }}</td>
                                    <td class="py-2">{{ $sr->service_type?->label() }}</td>
                                    <td class="py-2"><span class="px-1.5 py-0.5 rounded-full bg-gray-100 text-xs">{{ $sr->status?->value ?? $sr->status }}</span></td>
                                    <td class="py-2">tour {{ $sr->current_round }}</td>
                                    <td class="py-2 text-right">
                                        @if ($sr->unlockedBy)
                                            débloqué par <a href="{{ route('admin.providers.show', $sr->unlockedBy->id) }}" class="text-brand hover:underline">{{ $sr->unlockedBy->email }}</a>
                                        @else
                                            <a href="{{ route('admin.service_requests.show', $sr->service_uid) }}" class="text-brand hover:underline">Voir</a>
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
                    <div class="bg-white rounded-xl border border-red-200 p-5">
                        <h3 class="font-display text-sm font-semibold text-red-700 mb-2">Suppression définitive</h3>
                        <form method="POST" action="{{ route('admin.requests.delete', $wr->wed_uid) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="confirm" required placeholder="{{ __('admin.requests.delete_confirm_label') }}" class="w-full px-3 py-2 rounded-lg border border-red-200 text-sm" />
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.requests.delete_reason_label') }}" class="w-full px-3 py-2 rounded-lg border border-red-200 text-sm" />
                            <button class="w-full py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">{{ __('admin.requests.delete_btn') }}</button>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
