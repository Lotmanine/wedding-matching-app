@php
    $steps = [
        1 => __('provider.registration.step_1'),
        2 => __('provider.registration.step_2'),
        3 => __('provider.registration.step_3'),
        4 => __('provider.registration.step_4'),
        5 => __('provider.registration.step_5'),
    ];
    $totalSteps = 5;
@endphp

<div class="bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-6 sm:p-10">

    {{-- Progress --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-2 text-xs">
            <span class="font-medium uppercase tracking-widest text-brand">
                {{ __('provider.registration.step_of', ['current' => $step, 'total' => $totalSteps]) }}
            </span>
            <span class="text-gray-400">{{ $steps[$step] ?? '' }}</span>
        </div>
        <div class="flex gap-1.5">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div @class(['h-1.5 flex-1 rounded-full', 'bg-brand' => $i <= $step, 'bg-emerald-100' => $i > $step])></div>
            @endfor
        </div>
    </div>

    @error('submission')
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">{{ $message }}</div>
    @enderror

    <form wire:submit.prevent="submit" class="space-y-5">

        {{-- STEP 1 --}}
        @if ($step === 1)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.full_name') }}</label>
                    <input type="text" wire:model="full_name" maxlength="100" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.email') }}</label>
                    <input type="email" wire:model="email" autocomplete="email" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.password') }}</label>
                    <input type="password" wire:model="password" autocomplete="new-password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.auth.password_confirm') }}</label>
                    <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.phone') }}</label>
                    <input type="tel" wire:model.blur="phone" placeholder="+213 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.whatsapp') }}</label>
                    <input type="tel" wire:model="whatsapp" placeholder="+213 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('whatsapp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:border-brand transition cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50">
                <input type="checkbox" wire:model="accept_terms" class="mt-0.5 w-5 h-5 rounded text-brand focus:ring-emerald-100" />
                <span class="text-sm text-gray-700">{{ __('provider.registration.accept_terms') }}</span>
            </label>
            @error('accept_terms') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-200 hover:border-brand transition cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50">
                <input type="checkbox" wire:model="accept_anpdp" class="mt-0.5 w-5 h-5 rounded text-brand focus:ring-emerald-100" />
                <span class="text-sm text-gray-700">{{ __('provider.registration.accept_anpdp') }}</span>
            </label>
            @error('accept_anpdp') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 2 --}}
        @if ($step === 2)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.business_name') }}</label>
                    <input type="text" wire:model="business_name" maxlength="100" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('business_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.business_type') }}</label>
                    <select wire:model="business_type" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">—</option>
                        @foreach (__('provider.registration.business_type_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('business_type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.trade_register') }}</label>
                    <input type="text" wire:model="trade_register" maxlength="50" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.years_experience') }}</label>
                    <input type="number" wire:model="years_experience" min="0" max="60" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.primary_wilaya') }}</label>
                    <select wire:model.live="primary_wilaya_code" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">—</option>
                        @foreach ($this->wilayas as $w)
                            <option value="{{ $w->code }}">{{ $w->code }} — {{ $w->name_fr }}</option>
                        @endforeach
                    </select>
                    @error('primary_wilaya_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.price_tier') }}</label>
                    <select wire:model="price_tier" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">—</option>
                        @foreach (__('provider.registration.price_tier_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('price_tier') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.min_price') }}</label>
                    <input type="number" wire:model="min_price" min="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.max_price') }}</label>
                    <input type="number" wire:model="max_price" min="0" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('max_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.description') }}</label>
                    <textarea wire:model="business_description" rows="3" maxlength="500" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition resize-none"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.languages') }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach (__('provider.registration.languages_options') as $code => $label)
                            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 cursor-pointer has-[:checked]:bg-emerald-50 has-[:checked]:border-brand transition">
                                <input type="checkbox" value="{{ $code }}" wire:model="languages" class="rounded text-brand">
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('languages') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- STEP 3 --}}
        @if ($step === 3)
            <p class="text-sm text-gray-500">{{ __('provider.registration.services_hint') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($this->serviceTypes as $svc)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 hover:border-brand cursor-pointer transition has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50">
                        <input type="checkbox" value="{{ $svc->value }}" wire:model="services" class="w-5 h-5 rounded text-brand focus:ring-emerald-100" />
                        <span class="text-sm font-medium text-gray-800">{{ $svc->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('services') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 4 --}}
        @if ($step === 4)
            <p class="text-sm text-gray-500">{{ __('provider.registration.coverage_hint') }}</p>
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100">
                @foreach ($this->wilayas as $w)
                    @php $isPrimary = $w->code === $primary_wilaya_code; $isSelected = in_array($w->code, $coverage_wilayas, true); @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3 {{ $isSelected ? 'bg-emerald-50/40' : '' }}">
                        <label class="flex items-center gap-3 flex-1 cursor-pointer">
                            <input type="checkbox"
                                   wire:click="toggleCoverageWilaya('{{ $w->code }}')"
                                   @checked($isSelected || $isPrimary)
                                   @disabled($isPrimary)
                                   class="w-5 h-5 rounded text-brand focus:ring-emerald-100" />
                            <span class="text-sm font-medium text-gray-800">{{ $w->code }} — {{ $w->name_fr }}</span>
                            @if ($isPrimary)
                                <span class="text-xs text-brand bg-emerald-100 px-2 py-0.5 rounded-full">{{ __('provider.registration.coverage_primary_badge') }}</span>
                            @endif
                        </label>
                        @if ($isSelected && ! $isPrimary)
                            <input type="number" min="0" step="500"
                                   wire:model="travel_fees.{{ $w->code }}"
                                   placeholder="DZD"
                                   class="w-28 px-2 py-1.5 text-sm rounded-lg border border-gray-200 focus:border-brand focus:ring-1 focus:ring-emerald-100 focus:outline-none"
                            />
                        @endif
                    </div>
                @endforeach
            </div>
            @error('coverage_wilayas') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 5 --}}
        @if ($step === 5)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.portfolio_urls_label') }}</label>
                <div class="space-y-2">
                    @foreach ($portfolio_urls as $i => $url)
                        <div wire:key="url-{{ $i }}" class="flex gap-2">
                            <input type="url" wire:model="portfolio_urls.{{ $i }}" placeholder="{{ __('provider.registration.portfolio_url_placeholder') }}"
                                   class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                            @if (count($portfolio_urls) > 1)
                                <button type="button" wire:click="removePortfolioUrl({{ $i }})" class="px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">✕</button>
                            @endif
                        </div>
                        @error('portfolio_urls.' . $i) <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    @endforeach
                </div>
                @if (count($portfolio_urls) < 5)
                    <button type="button" wire:click="addPortfolioUrl" class="mt-3 text-sm text-brand font-medium hover:underline">{{ __('provider.registration.add_url') }}</button>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.portfolio_images') }}</label>
                <input type="file" wire:model="portfolio_images" multiple accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-brand file:font-semibold hover:file:bg-emerald-100" />
                <div wire:loading wire:target="portfolio_images" class="text-xs text-brand mt-2">Téléversement en cours…</div>
                @error('portfolio_images.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                @if (count($portfolio_images ?? []) > 0)
                    <div class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach ($portfolio_images as $img)
                            @if ($img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                <img src="{{ $img->temporaryUrl() }}" class="w-full h-20 object-cover rounded-lg border border-gray-200" />
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.logo') }}</label>
                <input type="file" wire:model="logo_upload" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-brand file:font-semibold hover:file:bg-emerald-100" />
                @error('logo_upload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @if ($logo_upload instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                    <img src="{{ $logo_upload->temporaryUrl() }}" class="mt-3 w-24 h-24 object-cover rounded-xl border border-gray-200" />
                @endif
            </div>
        @endif

        {{-- Navigation --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <button type="button" wire:click="previousStep"
                    @class(['px-5 py-2.5 rounded-xl text-sm font-medium transition', 'text-gray-600 hover:text-brand hover:bg-emerald-50' => $step > 1, 'invisible' => $step === 1])>
                {{ __('provider.registration.previous') }}
            </button>

            @if ($step < $totalSteps)
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition disabled:opacity-60">
                    {{ __('provider.registration.next') }}
                </button>
            @else
                <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="submit">{{ __('provider.registration.submit') }}</span>
                    <span wire:loading wire:target="submit">…</span>
                </button>
            @endif
        </div>
    </form>
</div>
