@extends('layouts.admin')

@section('content')
    @php $prof = $user->providerProfile; @endphp
    <div class="max-w-6xl">
        <nav class="text-sm text-[#9A958D] mb-3">
            <a href="{{ route('admin.providers.index') }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors"><x-z.icon name="arrow_back" class="text-base" /> {{ __('admin.providers.title') }}</a>
        </nav>

        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ $prof?->business_name ?? $user->name }}</h1>
                <p class="text-sm text-[#6B665E] mt-0.5">{{ $user->email }} · {{ $user->phone }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($prof?->verified)
                    <form method="POST" action="{{ route('admin.providers.unverify', $user->id) }}">@csrf
                        <x-z.btn type="submit" variant="outline" size="sm" icon="remove_done">{{ __('admin.providers.action_unverify') }}</x-z.btn>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.providers.verify', $user->id) }}">@csrf
                        <x-z.btn type="submit" variant="accent" size="sm" icon="verified">{{ __('admin.providers.action_verify') }}</x-z.btn>
                    </form>
                @endif

                @if ($prof?->suspended_at)
                    <form method="POST" action="{{ route('admin.providers.reactivate', $user->id) }}">@csrf
                        <x-z.btn type="submit" variant="primary" size="sm" icon="play_circle">{{ __('admin.providers.action_reactivate') }}</x-z.btn>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-4">Profil</h2>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Wilaya</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $prof?->primary_wilaya_code }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Type</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $prof?->business_type }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Tarif</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $prof?->price_tier }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Expérience</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $prof?->years_experience ?? 'Non renseigné' }} ans</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Vérifié</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $prof?->verified ? 'Oui' : 'Non' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D]">Statut</dt><dd class="text-[#1A1A1A] mt-0.5">{{ $user->status }}</dd></div>
                    </dl>
                    @if ($prof?->business_description)
                        <p class="mt-4 text-sm text-[#6B665E] border-t border-[#D4CFC7]/30 pt-4">{{ $prof->business_description }}</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-4">Services & Couverture</h2>
                    <div class="flex flex-wrap gap-1 mb-4">
                        @foreach ($user->providerServices as $svc)
                            <span class="text-xs bg-[#F0D9B5]/40 text-[#7A5530] px-2.5 py-1 rounded-full border border-[#C8956C]/30 font-medium">{{ $svc->service_type?->label() ?? $svc->service_type }}</span>
                        @endforeach
                    </div>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/30"><th class="text-left py-2">Wilaya</th><th class="text-right py-2">Frais DA</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[#D4CFC7]/20">
                            @foreach ($user->providerCoverage as $cov)
                                <tr><td class="py-2 text-[#1A1A1A]">{{ $cov->wilaya_code }}</td><td class="text-right font-mono text-[#6B665E]">{{ $cov->travel_fee_dzd }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($purchases->isNotEmpty())
                    <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                        <h2 class="font-serif text-lg font-bold text-[#1A1A1A] mb-4">Derniers achats</h2>
                        <table class="w-full text-xs">
                            <tbody class="divide-y divide-[#D4CFC7]/20">
                                @foreach ($purchases as $p)
                                    <tr>
                                        <td class="py-2 font-mono text-[#6B665E]">{{ $p->purchase_uid }}</td>
                                        <td class="py-2 text-[#1A1A1A]">{{ $p->unlockPack?->name ?? 'Non renseigné' }}</td>
                                        <td class="py-2 text-[#1A1A1A]">{{ number_format($p->amount_dzd, 0, ',', ' ') }} DA</td>
                                        <td class="py-2"><x-z.badge variant="default">{{ $p->status }}</x-z.badge></td>
                                        <td class="py-2 text-[#9A958D] text-right">{{ $p->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <aside class="space-y-4">
                <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-2">Crédits</h3>
                    <p class="font-serif text-3xl font-bold text-[#1A1A1A]">{{ $prof?->credit_balance ?? 0 }}</p>
                    <form method="POST" action="{{ route('admin.providers.credits.adjust', $user->id) }}" class="mt-5 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.providers.adjust_label') }}</label>
                            <input type="number" name="amount" required step="1" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-[#9A958D] mb-1.5">{{ __('admin.providers.adjust_reason') }}</label>
                            <input type="text" name="reason" required maxlength="500" class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                        </div>
                        <x-z.btn type="submit" variant="primary" size="sm" class="w-full">{{ __('admin.providers.action_adjust') }}</x-z.btn>
                    </form>
                </div>

                @if (! $prof?->suspended_at)
                    <div class="bg-white rounded-xl border border-red-100 shadow-sm p-6">
                        <h3 class="flex items-center gap-2 font-serif text-sm font-bold text-[#A84936] mb-3"><x-z.icon name="block" class="text-lg" /> {{ __('admin.providers.action_suspend') }}</h3>
                        <form method="POST" action="{{ route('admin.providers.suspend', $user->id) }}" class="space-y-2.5">
                            @csrf
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.providers.suspend_reason') }}" class="w-full px-4 py-2.5 bg-white border border-red-200 rounded-lg text-sm outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15 transition-colors" />
                            <x-z.btn type="submit" variant="danger" size="sm" class="w-full">{{ __('admin.providers.action_suspend') }}</x-z.btn>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
