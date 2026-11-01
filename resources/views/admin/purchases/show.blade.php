@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <nav class="text-sm text-[#9A958D] mb-3"><a href="{{ route('admin.purchases.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('admin.purchases.title') }}</a></nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ $purchase->purchase_uid }}</h1>
        <p class="text-sm text-[#6B665E] mt-0.5 mb-6">{{ $purchase->user?->email }} · {{ $purchase->created_at?->isoFormat('LL, HH:mm') }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Status') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->status }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Pack') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->unlockPack?->name }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Amount') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ \App\Support\Money::format($purchase->amount, $purchase->currency) }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('admin.common.credits') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->unlocks_count }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Checkout ID') }}</dt><dd class="font-mono text-xs text-[#6B665E] mt-0.5">{{ $purchase->gateway_checkout_id ?? __('Not specified') }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Payment ID') }}</dt><dd class="font-mono text-xs text-[#6B665E] mt-0.5">{{ $purchase->gateway_payment_id ?? __('Not specified') }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Paid on') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? __('Not specified') }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Failure') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->failure_reason ?? __('Not specified') }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Method') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->payment_method === 'offline_transfer' ? __('Bank transfer') : __('Online (Chargily)') }}</dd></div>
                    @if ($purchase->payment_method === 'offline_transfer')
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Transfer reference') }}</dt><dd class="font-mono text-xs text-[#1A1A1A] mt-0.5">{{ $purchase->payment_reference ?? __('Not specified') }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">{{ __('Proof of payment') }}</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $purchase->proof_path ? __('Provided') : __('None') }}</dd></div>
                    @endif
                </dl>

                @if ($purchase->gateway_metadata)
                    <details class="mt-5 text-xs">
                        <summary class="cursor-pointer text-[#9A958D] hover:text-[#C8956C] transition-colors font-medium">{{ __('Technical payment data') }}</summary>
                        <pre class="mt-2 p-3 rounded-lg bg-[#F7F4F0] border border-[#D4CFC7]/50 overflow-x-auto text-[#6B665E]">{{ json_encode($purchase->gateway_metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </div>

            <aside class="space-y-5">
                {{-- Offline transfer verification queue action --}}
                @if ($purchase->status === 'pending_verification')
                    <div class="bg-white rounded-xl border border-[#C8956C]/40 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#7A5530] mb-2"><x-z.icon name="account_balance" class="text-lg text-[#C8956C]" /> {{ __('Transfer to verify') }}</h3>
                        <p class="text-xs text-[#6B665E] mb-4">{{ __('Confirm once you have seen the funds arrive. The credits will then be added to the provider.') }}</p>

                        <form method="POST" action="{{ route('admin.purchases.confirm_offline', $purchase->purchase_uid) }}" class="mb-3">
                            @csrf
                            <x-z.btn type="submit" variant="accent" size="sm" class="w-full justify-center"><x-z.icon name="check" class="text-base" /> {{ __('Confirm transfer') }}</x-z.btn>
                        </form>

                        <details class="text-xs">
                            <summary class="cursor-pointer text-[#9A958D] hover:text-[#A84936] transition-colors font-medium">{{ __('Reject this transfer') }}</summary>
                            <form method="POST" action="{{ route('admin.purchases.reject_offline', $purchase->purchase_uid) }}" class="space-y-2.5 mt-3">
                                @csrf
                                <input type="text" name="reason" required maxlength="500" placeholder="{{ __('Reason for rejection') }}" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15 transition-colors" />
                                <x-z.btn type="submit" variant="danger" size="sm" class="w-full justify-center">{{ __('Reject') }}</x-z.btn>
                            </form>
                        </details>
                    </div>
                @endif

                @if (! in_array($purchase->status, ['paid', 'pending_verification'], true))
                    <div class="bg-white rounded-xl border border-[#C8956C]/30 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#7A5530] mb-3"><x-z.icon name="build" class="text-lg text-[#C8956C]" /> {{ __('Manual operation') }}</h3>
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
