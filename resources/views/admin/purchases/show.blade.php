@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <nav class="text-sm text-gray-500 mb-3"><a href="{{ route('admin.purchases.index') }}" class="hover:text-brand">← Achats</a></nav>

        <h1 class="font-display text-2xl font-bold">{{ $purchase->purchase_uid }}</h1>
        <p class="text-sm text-gray-500 mb-4">{{ $purchase->user?->email }} · {{ $purchase->created_at?->isoFormat('LL, HH:mm') }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500 text-xs uppercase">Statut</dt><dd>{{ $purchase->status }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Pack</dt><dd>{{ $purchase->unlockPack?->name }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Montant</dt><dd>{{ number_format($purchase->amount_dzd, 0, ',', ' ') }} DZD</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Crédits</dt><dd>{{ $purchase->unlocks_count }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Checkout ID</dt><dd class="font-mono text-xs">{{ $purchase->gateway_checkout_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Payment ID</dt><dd class="font-mono text-xs">{{ $purchase->gateway_payment_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Payé le</dt><dd>{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500 text-xs uppercase">Échec</dt><dd>{{ $purchase->failure_reason ?? '—' }}</dd></div>
                </dl>

                @if ($purchase->gateway_metadata)
                    <details class="mt-4 text-xs">
                        <summary class="cursor-pointer text-gray-500 hover:text-gray-800">Gateway metadata</summary>
                        <pre class="mt-2 p-3 rounded bg-gray-50 border border-gray-200 overflow-x-auto">{{ json_encode($purchase->gateway_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </div>

            <aside>
                @if ($purchase->status !== 'paid')
                    <div class="bg-white rounded-xl border border-amber-200 p-5">
                        <h3 class="font-display text-sm font-semibold text-amber-800 mb-2">Opération manuelle</h3>
                        <form method="POST" action="{{ route('admin.purchases.mark_paid', $purchase->purchase_uid) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.purchases.mark_paid_reason') }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                            <button class="w-full py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold transition">{{ __('admin.purchases.mark_paid_btn') }}</button>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
