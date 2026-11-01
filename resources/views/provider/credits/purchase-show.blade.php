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
                    <dd class="mt-1 font-medium text-[#1A1A1A]">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? __('Non renseigné') }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.status') }}</dt>
                    <dd class="mt-1">
                        @php $badge = match ($purchase->status) {
                            'paid'      => ['success', __('Payé')],
                            'pending'   => ['warning', __('En attente')],
                            'pending_verification' => ['warning', __('credits.offline.pending_status')],
                            'failed'    => ['danger', __('Échoué')],
                            'cancelled' => ['muted', __('Annulé')],
                            default     => ['default', $purchase->status],
                        }; @endphp
                        <x-z.badge :variant="$badge[0]">{{ $badge[1] }}</x-z.badge>
                    </dd>
                </div>
                <div>
                    {{-- A versement has no Chargily reference: show the reference the provider declared --}}
                    @if ($purchase->payment_method === \App\Services\Payments\PaymentSettings::METHOD_OFFLINE)
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.transfer_ref') }}</dt>
                        <dd class="mt-1 font-mono text-[#6B665E] text-xs"><span>{{ $purchase->payment_reference ?? __('Non renseigné') }}</span></dd>
                    @else
                        <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('credits.receipt.gateway_ref') }}</dt>
                        <dd class="mt-1 font-mono text-[#6B665E] text-xs"><span>{{ $purchase->gateway_payment_id ?? $purchase->gateway_checkout_id ?? __('Non renseigné') }}</span></dd>
                    @endif
                </div>
                @if ($purchase->status === 'pending_verification')
                    <div class="sm:col-span-2 p-3 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 text-sm">
                        {{ __('credits.offline.pending_note') }}
                    </div>
                @endif
                <div class="sm:col-span-2 pt-5 border-t border-[#D4CFC7]/30">
                    <dt class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __((string) $purchase->unlockPack?->name) }}</dt>
                    <dd class="mt-2 flex items-baseline justify-between">
                        <span class="font-serif text-lg font-semibold text-[#1A1A1A]">{{ trans_choice('credits.count', (int) $purchase->unlocks_count, ['count' => (int) $purchase->unlocks_count]) }}</span>
                        <span class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ \App\Support\Money::format($purchase->amount, $purchase->currency) }}</span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
