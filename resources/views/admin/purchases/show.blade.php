@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.purchases.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> Achats</a></nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ $purchase->purchase_uid }}</h1>
        <p class="text-sm text-[#6B665E] mt-0.5 mb-6">{{ $purchase->user?->email }} · {{ $purchase->created_at?->isoFormat('LL, HH:mm') }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Statut</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->status }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Pack</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->unlockPack?->name }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Montant</dt><dd class="text-[#1A1A1A] mt-0.5">{{ number_format($purchase->amount_dzd, 0, ',', ' ') }} DA</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Crédits</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->unlocks_count }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Checkout ID</dt><dd class="font-mono text-xs text-[#6B665E] mt-0.5">{{ $purchase->gateway_checkout_id ?? 'Non renseigné' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Payment ID</dt><dd class="font-mono text-xs text-[#6B665E] mt-0.5">{{ $purchase->gateway_payment_id ?? 'Non renseigné' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Payé le</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? 'Non renseigné' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Échec</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->failure_reason ?? 'Non renseigné' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Méthode</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->payment_method === 'offline_versement' ? 'Versement (CCP / Banque)' : 'En ligne (Chargily)' }}</dd></div>
                    @if ($purchase->payment_method === 'offline_versement')
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Référence versement</dt><dd class="font-mono text-xs text-[#1A1A1A] mt-0.5">{{ $purchase->payment_reference ?? 'Non renseigné' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Justificatif</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->proof_path ? 'Fourni' : 'Aucun' }}</dd></div>
                    @endif
                </dl>

                @if ($purchase->gateway_metadata)
                    <details class="mt-5 text-xs">
                        <summary class="cursor-pointer text-[#9A958D] hover:text-[#C8956C] transition-colors font-medium">Données techniques du paiement</summary>
                        <pre class="mt-2 p-3 rounded-lg bg-[#F7F4F0] border border-[#D4CFC7]/50 overflow-x-auto text-[#6B665E]">{{ json_encode($purchase->gateway_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </div>

            <aside class="space-y-5">
                {{-- Offline versement verification queue action --}}
                @if ($purchase->status === 'pending_verification')
                    <div class="bg-white rounded-xl border border-[#C8956C]/40 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#7A5530] mb-2"><x-z.icon name="account_balance" class="text-lg text-[#C8956C]" /> Versement à vérifier</h3>
                        <p class="text-xs text-[#6B665E] mb-4">Confirmez après avoir constaté la réception des fonds. Les crédits seront alors ajoutés au prestataire.</p>

                        <form method="POST" action="{{ route('admin.purchases.confirm_offline', $purchase->purchase_uid) }}" class="mb-3">
                            @csrf
                            <x-z.btn type="submit" variant="accent" size="sm" class="w-full justify-center"><x-z.icon name="check" class="text-base" /> Confirmer le versement</x-z.btn>
                        </form>

                        <details class="text-xs">
                            <summary class="cursor-pointer text-[#9A958D] hover:text-[#A84936] transition-colors font-medium">Rejeter ce versement</summary>
                            <form method="POST" action="{{ route('admin.purchases.reject_offline', $purchase->purchase_uid) }}" class="space-y-2.5 mt-3">
                                @csrf
                                <input type="text" name="reason" required maxlength="500" placeholder="Motif du rejet" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15 transition-colors" />
                                <x-z.btn type="submit" variant="danger" size="sm" class="w-full justify-center">Rejeter</x-z.btn>
                            </form>
                        </details>
                    </div>
                @endif

                @if (! in_array($purchase->status, ['paid', 'pending_verification'], true))
                    <div class="bg-white rounded-xl border border-[#C8956C]/30 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#7A5530] mb-3"><x-z.icon name="build" class="text-lg text-[#C8956C]" /> Opération manuelle</h3>
                        <form method="POST" action="{{ route('admin.purchases.mark_paid', $purchase->purchase_uid) }}" class="space-y-2.5">
                            @csrf
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.purchases.mark_paid_reason') }}" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                            <x-z.btn type="submit" variant="accent" size="sm" class="w-full">{{ __('admin.purchases.mark_paid_btn') }}</x-z.btn>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
