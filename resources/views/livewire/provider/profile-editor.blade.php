<div class="bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-6 sm:p-8 space-y-10">

    @error('submission')
        <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">{{ $message }}</div>
    @enderror

    <form wire:submit.prevent="save" class="space-y-10">

        {{-- Personal --}}
        <section>
            <h2 class="font-display text-lg font-semibold text-brand mb-4">{{ __('provider.profile.section_personal') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.full_name') }}</label>
                    <input type="text" wire:model="full_name" maxlength="100" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.phone') }}</label>
                    <input type="tel" wire:model="phone" placeholder="+213 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.whatsapp') }}</label>
                    <input type="tel" wire:model="whatsapp" placeholder="+213 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                    @error('whatsapp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('provider.auth.email') }}</label>
                    <input type="email" value="{{ $email }}" disabled class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-500" />
                </div>
            </div>
        </section>

        {{-- Business --}}
        <section>
            <h2 class="font-display text-lg font-semibold text-brand mb-4">{{ __('provider.profile.section_business') }}</h2>
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
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('provider.registration.price_tier') }}</label>
                    <select wire:model="price_tier" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none">
                        <option value="">—</option>
                        @foreach (__('provider.registration.price_tier_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
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
                </div>
            </div>
        </section>

        {{-- Services --}}
        <section>
            <h2 class="font-display text-lg font-semibold text-brand mb-4">{{ __('provider.profile.section_services') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($this->serviceTypes as $svc)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 hover:border-brand cursor-pointer transition has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50">
                        <input type="checkbox" value="{{ $svc->value }}" wire:model="services" class="w-5 h-5 rounded text-brand focus:ring-emerald-100" />
                        <span class="text-sm font-medium">{{ $svc->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('services') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        {{-- Coverage --}}
        <section>
            <h2 class="font-display text-lg font-semibold text-brand mb-4">{{ __('provider.profile.section_coverage') }}</h2>
            <p class="text-sm text-gray-500 mb-3">{{ __('provider.registration.coverage_hint') }}</p>
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
                            <span class="text-sm font-medium">{{ $w->code }} — {{ $w->name_fr }}</span>
                            @if ($isPrimary)
                                <span class="text-xs text-brand bg-emerald-100 px-2 py-0.5 rounded-full">{{ __('provider.registration.coverage_primary_badge') }}</span>
                            @endif
                        </label>
                        @if ($isSelected && ! $isPrimary)
                            <input type="number" min="0" step="500" wire:model="travel_fees.{{ $w->code }}" placeholder="DZD" class="w-28 px-2 py-1.5 text-sm rounded-lg border border-gray-200" />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Portfolio --}}
        <section>
            <h2 class="font-display text-lg font-semibold text-brand mb-4">{{ __('provider.profile.section_portfolio') }}</h2>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.portfolio_urls_label') }}</label>
                <div class="space-y-2">
                    @foreach ($portfolio_urls as $i => $url)
                        <div wire:key="p-url-{{ $i }}" class="flex gap-2">
                            <input type="url" wire:model="portfolio_urls.{{ $i }}" placeholder="{{ __('provider.registration.portfolio_url_placeholder') }}"
                                   class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition" />
                            <button type="button" wire:click="removePortfolioUrl({{ $i }})" class="px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">✕</button>
                        </div>
                    @endforeach
                </div>
                @if (count($portfolio_urls) < 5)
                    <button type="button" wire:click="addPortfolioUrl" class="mt-2 text-sm text-brand hover:underline">{{ __('provider.registration.add_url') }}</button>
                @endif
            </div>

            @if ($logo_url_existing)
                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('provider.profile.logo_current') }}</p>
                    <div class="flex items-center gap-3">
                        <img src="{{ $logo_url_existing }}" class="w-20 h-20 object-cover rounded-xl border border-gray-200" />
                        <button type="button" wire:click="deleteLogo" class="text-sm text-red-600 hover:underline">{{ __('provider.profile.delete_logo') }}</button>
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.registration.logo') }}</label>
                <input type="file" wire:model="logo_new" accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-brand file:font-semibold" />
                @error('logo_new') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if (count($portfolio_images_existing ?? []) > 0)
                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('provider.profile.existing_images') }}</p>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                        @foreach ($portfolio_images_existing as $i => $img)
                            <div class="relative group" wire:key="exist-img-{{ $i }}">
                                <img src="{{ $img }}" class="w-full h-24 object-cover rounded-lg border border-gray-200" />
                                <button type="button" wire:click="deleteExistingImage({{ $i }})" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white text-xs opacity-0 group-hover:opacity-100 transition">✕</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('provider.profile.new_images') }}</label>
                <input type="file" wire:model="portfolio_images_new" multiple accept="image/*" class="w-full text-sm text-gray-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-brand file:font-semibold" />
                @error('portfolio_images_new.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="submit" wire:loading.attr="disabled"
                    class="px-6 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">{{ __('provider.profile.save') }}</span>
                <span wire:loading wire:target="save">…</span>
            </button>
        </div>
    </form>
</div>
