@extends('layouts.admin')

@section('content')
    @php $prof = $user->providerProfile; @endphp
    <div class="max-w-6xl">
        <nav class="text-sm text-gray-500 mb-3">
            <a href="{{ route('admin.providers.index') }}" class="hover:text-brand">← {{ __('admin.providers.title') }}</a>
        </nav>

        <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
            <div>
                <h1 class="font-display text-2xl font-bold text-gray-900">{{ $prof?->business_name ?? $user->name }}</h1>
                <p class="text-sm text-gray-500">{{ $user->email }} · {{ $user->phone }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($prof?->verified)
                    <form method="POST" action="{{ route('admin.providers.unverify', $user->id) }}">@csrf
                        <button class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm hover:border-amber-400 hover:text-amber-800 transition">{{ __('admin.providers.action_unverify') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.providers.verify', $user->id) }}">@csrf
                        <button class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">{{ __('admin.providers.action_verify') }}</button>
                    </form>
                @endif

                @if ($prof?->suspended_at)
                    <form method="POST" action="{{ route('admin.providers.reactivate', $user->id) }}">@csrf
                        <button class="px-3 py-1.5 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">{{ __('admin.providers.action_reactivate') }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-display text-lg font-semibold mb-3">Profil</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-gray-500 text-xs uppercase">Wilaya</dt><dd>{{ $prof?->primary_wilaya_code }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Type</dt><dd>{{ $prof?->business_type }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Tarif</dt><dd>{{ $prof?->price_tier }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Expérience</dt><dd>{{ $prof?->years_experience ?? '—' }} ans</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Vérifié</dt><dd>{{ $prof?->verified ? 'Oui' : 'Non' }}</dd></div>
                        <div><dt class="text-gray-500 text-xs uppercase">Statut</dt><dd>{{ $user->status }}</dd></div>
                    </dl>
                    @if ($prof?->business_description)
                        <p class="mt-4 text-sm text-gray-600 border-t border-gray-100 pt-3">{{ $prof->business_description }}</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="font-display text-lg font-semibold mb-3">Services & Couverture</h2>
                    <div class="flex flex-wrap gap-1 mb-3">
                        @foreach ($user->providerServices as $svc)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-brand">{{ $svc->service_type?->label() ?? $svc->service_type }}</span>
                        @endforeach
                    </div>
                    <table class="w-full text-xs">
                        <thead class="text-gray-500 uppercase"><tr><th class="text-left py-1">Wilaya</th><th class="text-right py-1">Frais DZD</th></tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($user->providerCoverage as $cov)
                                <tr><td class="py-1">{{ $cov->wilaya_code }}</td><td class="text-right font-mono">{{ $cov->travel_fee_dzd }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($purchases->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-200 p-5">
                        <h2 class="font-display text-lg font-semibold mb-3">Derniers achats</h2>
                        <table class="w-full text-xs">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($purchases as $p)
                                    <tr>
                                        <td class="py-1.5 font-mono">{{ $p->purchase_uid }}</td>
                                        <td class="py-1.5">{{ $p->unlockPack?->name ?? '—' }}</td>
                                        <td class="py-1.5">{{ number_format($p->amount_dzd, 0, ',', ' ') }} DZD</td>
                                        <td class="py-1.5"><span class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-100">{{ $p->status }}</span></td>
                                        <td class="py-1.5 text-gray-400 text-right">{{ $p->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <aside class="space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-display text-lg font-semibold mb-3">Crédits</h3>
                    <p class="text-3xl font-display font-bold text-brand">{{ $prof?->credit_balance ?? 0 }}</p>
                    <form method="POST" action="{{ route('admin.providers.credits.adjust', $user->id) }}" class="mt-4 space-y-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('admin.providers.adjust_label') }}</label>
                            <input type="number" name="amount" required step="1" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('admin.providers.adjust_reason') }}</label>
                            <input type="text" name="reason" required maxlength="500" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                        </div>
                        <button class="w-full py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">{{ __('admin.providers.action_adjust') }}</button>
                    </form>
                </div>

                @if (! $prof?->suspended_at)
                    <div class="bg-white rounded-xl border border-red-200 p-5">
                        <h3 class="font-display text-sm font-semibold text-red-700 mb-2">{{ __('admin.providers.action_suspend') }}</h3>
                        <form method="POST" action="{{ route('admin.providers.suspend', $user->id) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" required maxlength="500" placeholder="{{ __('admin.providers.suspend_reason') }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm" />
                            <button class="w-full py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition">{{ __('admin.providers.action_suspend') }}</button>
                        </form>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
