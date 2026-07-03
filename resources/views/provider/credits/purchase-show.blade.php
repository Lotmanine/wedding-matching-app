@extends('layouts.provider')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('provider.credits.history') }}" class="flex items-center gap-2 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors font-medium">
                <x-z.icon name="arrow_back" class="text-lg" /> {{ __('credits.history_title') }}
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 sm:p-10">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('credits.receipt.title') }}</h1>
                    <p class="mt-1 text-xs text-[#9A958D] font-mono">{{ $purchase->purchase_uid }}</p>
                </div>
                <x-z.btn :href="route('provider.credits.purchase.show', $purchase->purchase_uid) . '?format=pdf'" variant="outline" size="sm" icon="picture_as_pdf">
                    {{ __('credits.receipt.download_pdf') }}
                </x-z.btn>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.issued_to') }}</dt>
                    <dd class="mt-1 font-medium text-[#1A1A1A]">{{ $purchase->user->name }}</dd>
                    <dd class="text-[#9A958D]">{{ $purchase->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.paid_on') }}</dt>
                    <dd class="mt-1 font-medium text-[#1A1A1A]">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? 'Non renseigné' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.status') }}</dt>
                    <dd class="mt-1">
                        @php $badge = match ($purchase->status) {
                            'paid'      => ['success', 'Payé'],
                            'pending'   => ['warning', 'En attente'],
                            'failed'    => ['danger', 'Échoué'],
                            'cancelled' => ['muted', 'Annulé'],
                            default     => ['default', $purchase->status],
                        }; @endphp
                        <x-z.badge :variant="$badge[0]">{{ $badge[1] }}</x-z.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.gateway_ref') }}</dt>
                    <dd class="mt-1 font-mono text-[#6B665E] text-xs">{{ $purchase->gateway_payment_id ?? $purchase->gateway_checkout_id ?? 'Non renseigné' }}</dd>
                </div>
                <div class="sm:col-span-2 pt-5 border-t border-[#D4CFC7]/30">
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ $purchase->unlockPack?->name }}</dt>
                    <dd class="mt-2 flex items-baseline justify-between">
                        <span class="font-serif text-lg font-semibold text-[#1A1A1A]">{{ $purchase->unlocks_count }} crédits</span>
                        <span class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ number_format($purchase->amount_dzd, 0, ',', ' ') }} DZD</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
