@extends('layouts.provider')

@section('content')
    <div class="max-w-3xl">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('provider.credits.history') }}" class="hover:text-brand">← {{ __('credits.history_title') }}</a>
        </nav>

        <div class="bg-white rounded-2xl border border-emerald-100/70 shadow-sm p-6 sm:p-10">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h1 class="font-display text-2xl font-bold text-gray-900">{{ __('credits.receipt.title') }}</h1>
                    <p class="mt-1 text-xs text-gray-400 font-mono">{{ $purchase->purchase_uid }}</p>
                </div>
                <a href="{{ route('provider.credits.purchase.show', $purchase->purchase_uid) }}?format=pdf"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border-2 border-emerald-200 hover:border-brand text-brand text-sm font-semibold transition">
                    📄 {{ __('credits.receipt.download_pdf') }}
                </a>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('credits.receipt.issued_to') }}</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $purchase->user->name }}</dd>
                    <dd class="text-gray-500">{{ $purchase->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('credits.receipt.paid_on') }}</dt>
                    <dd class="mt-0.5 font-medium text-gray-900">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('credits.receipt.status') }}</dt>
                    <dd class="mt-0.5">
                        @php $badge = match ($purchase->status) {
                            'paid'      => ['bg-emerald-100 text-brand', 'Payé'],
                            'pending'   => ['bg-amber-100 text-amber-800', 'En attente'],
                            'failed'    => ['bg-red-100 text-red-700', 'Échoué'],
                            'cancelled' => ['bg-gray-100 text-gray-600', 'Annulé'],
                            default     => ['bg-gray-100 text-gray-700', $purchase->status],
                        }; @endphp
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $badge[0] }}">{{ $badge[1] }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-gray-500">{{ __('credits.receipt.gateway_ref') }}</dt>
                    <dd class="mt-0.5 font-mono text-gray-600 text-xs">{{ $purchase->gateway_payment_id ?? $purchase->gateway_checkout_id ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2 pt-4 border-t border-emerald-100/60">
                    <dt class="text-xs uppercase tracking-wider text-gray-500">{{ $purchase->unlockPack?->name }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between">
                        <span class="font-display text-lg font-semibold text-gray-900">{{ $purchase->unlocks_count }} crédits</span>
                        <span class="font-display text-2xl font-bold text-brand">{{ number_format($purchase->amount_dzd, 0, ',', ' ') }} DZD</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
