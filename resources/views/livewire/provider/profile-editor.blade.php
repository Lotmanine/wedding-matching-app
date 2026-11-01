<div class="bg-white rounded-2xl shadow-sm border border-[#D4CFC7]/50 p-6 sm:p-8 space-y-10">

    @error('submission')
        <div class="p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-xl shrink-0" aria-hidden="true">error</span>{{ $message }}
        </div>
    @enderror

    <form wire:submit.prevent="save" class="space-y-10">

        {{-- Personal --}}
        <section>
            <div class="flex items-center gap-3 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('provider.profile.section_personal') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.full_name') }}</label>
                    <input type="text" wire:model="full_name" maxlength="100" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    @error('full_name') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.phone') }}</label>
                    <input type="tel" wire:model="phone" placeholder="+12125550123" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    <p class="mt-1 text-xs text-[#9A958D]">{!! __('provider.registration.phone_hint', ['prefix' => '+1 / +33', 'example' => '<span>+12125550123</span>']) !!}</p>
                    @error('phone') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.whatsapp') }}</label>
                    <input type="tel" wire:model="whatsapp" placeholder="+12125550123" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    <p class="mt-1 text-xs text-[#9A958D]">{!! __('provider.registration.phone_hint', ['prefix' => '+1 / +33', 'example' => '<span>+12125550123</span>']) !!}</p>
                    @error('whatsapp') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#9A958D] mb-1.5">{{ __('provider.auth.email') }}</label>
                    <input type="email" value="{{ $email }}" disabled class="w-full px-4 py-3.5 bg-[#F7F4F0] border border-[#D4CFC7]/60 rounded-lg text-sm text-[#9A958D]" />
                </div>
            </div>
        </section>

        {{-- Business --}}
        <section>
            <div class="flex items-center gap-3 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('provider.profile.section_business') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.business_name') }}</label>
                    <input type="text" wire:model="business_name" maxlength="100" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    @error('business_name') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.business_type') }}</label>
                    <select wire:model="business_type" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('Sélectionner…') }}</option>
                        @foreach (__('provider.registration.business_type_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.trade_register') }}</label>
                    <input type="text" wire:model="trade_register" maxlength="50" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.years_experience') }}</label>
                    <input type="number" wire:model="years_experience" min="0" max="60" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.primary_city') }}</label>
                    <select wire:model.live="primary_city_code" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('Sélectionner…') }}</option>
                        @foreach ($this->cities as $w)
                            <option value="{{ $w->code }}">{{ $w->code }} · {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.price_tier') }}</label>
                    <select wire:model="price_tier" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('Sélectionner…') }}</option>
                        @foreach (__('provider.registration.price_tier_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.min_price') }}</label>
                    <input type="number" wire:model="min_price" min="0" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.max_price') }}</label>
                    <input type="number" wire:model="max_price" min="0" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    @error('max_price') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <p class="sm:col-span-2 -mt-2 text-xs text-[#9A958D]">{{ __('provider.registration.price_hint') }}</p>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.description') }}</label>
                    <textarea wire:model="business_description" rows="3" maxlength="500" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all resize-none"></textarea>
                    <p class="mt-1 text-xs text-[#9A958D]">{{ __('provider.registration.description_hint') }}</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.registration.languages') }}</label>
                    <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.registration.languages_hint') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach (__('provider.registration.languages_options') as $code => $label)
                            <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-[#D4CFC7]/60 cursor-pointer hover:border-[#C8956C]/40 transition-colors has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:border-[#C8956C]">
                                <input type="checkbox" value="{{ $code }}" wire:model="languages" class="w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30">
                                <span class="text-sm text-[#1A1A1A]">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- Services --}}
        <section>
            <div class="flex items-center gap-3 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('provider.profile.section_services') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($this->serviceTypes as $svc)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[#D4CFC7]/60 hover:border-[#C8956C]/40 cursor-pointer transition-colors has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/30">
                        <input type="checkbox" value="{{ $svc->value }}" wire:model="services" class="w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                        <span class="text-sm font-semibold text-[#1A1A1A]">{{ $svc->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('services') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
        </section>

        {{-- Coverage --}}
        <section>
            <div class="flex items-center gap-3 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('provider.profile.section_coverage') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>
            <p class="text-sm text-[#6B665E] mb-3">{{ __('provider.registration.coverage_hint') }}</p>
            <div class="max-h-96 overflow-y-auto border border-[#D4CFC7]/60 rounded-xl divide-y divide-[#D4CFC7]/30">
                @foreach ($this->cities as $w)
                    @php $isPrimary = $w->code === $primary_city_code; $isSelected = in_array($w->code, $coverage_cities, true); @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3 {{ $isSelected ? 'bg-[#F0D9B5]/20' : '' }}">
                        <label class="flex items-center gap-3 flex-1 cursor-pointer">
                            <input type="checkbox"
                                   wire:click="toggleCoverageCity('{{ $w->code }}')"
                                   @checked($isSelected || $isPrimary)
                                   @disabled($isPrimary)
                                   class="w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            <span class="text-sm font-medium text-[#1A1A1A]">{{ $w->code }} · {{ $w->name }}</span>
                            @if ($isPrimary)
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#7A5530] bg-[#F0D9B5] px-2 py-0.5 rounded-full">{{ __('provider.registration.coverage_primary_badge') }}</span>
                            @endif
                        </label>
                        @if ($isSelected && ! $isPrimary)
                            <input type="number" min="0" step="10" wire:model="travel_fees.{{ $w->code }}" placeholder="{{ \App\Support\Money::symbol(\App\Support\Money::currencyForCity($primary_city_code ?: null)) }}" aria-label="{{ __('provider.registration.coverage_travel_fee') }}" title="{{ __('provider.registration.coverage_travel_fee') }}" class="w-28 px-2 py-1.5 text-sm bg-white rounded-lg border border-[#D4CFC7] outline-none focus:border-[#C8956C] focus:ring-1 focus:ring-[#C8956C]/15" />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Portfolio --}}
        <section>
            <div class="flex items-center gap-3 mb-5">
                <span class="text-[10px] font-bold tracking-widest text-[#9A958D] uppercase">{{ __('provider.profile.section_portfolio') }}</span>
                <div class="h-px flex-1 bg-[#D4CFC7]/40"></div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.profile.portfolio_urls_label') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.profile.portfolio_urls_hint') }}</p>
                <div class="space-y-2">
                    @foreach ($portfolio_urls as $i => $url)
                        <div wire:key="p-url-{{ $i }}" class="flex gap-2">
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9A958D] text-lg pointer-events-none" aria-hidden="true">link</span>
                                <input type="url" wire:model="portfolio_urls.{{ $i }}" placeholder="{{ __('provider.registration.portfolio_url_placeholder') }}"
                                       class="w-full pl-10 pr-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                            </div>
                            <button type="button" wire:click="removePortfolioUrl({{ $i }})" aria-label="{{ __('provider.registration.remove_url') }}" title="{{ __('provider.registration.remove_url') }}" class="w-10 h-[46px] rounded-lg flex items-center justify-center shrink-0 text-[#9A958D] hover:text-[#A84936] hover:bg-red-50 transition-colors">
                                <span class="material-symbols-outlined text-xl" aria-hidden="true">remove_circle_outline</span>
                            </button>
                        </div>
                    @endforeach
                </div>
                @if (count($portfolio_urls) < 5)
                    <button type="button" wire:click="addPortfolioUrl" class="mt-2 inline-flex items-center gap-1 text-sm font-bold text-[#C8956C] hover:text-[#b8845b] transition-colors">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">add_circle</span>{{ __('provider.registration.add_url') }}
                    </button>
                @endif
            </div>

            @if ($logo_url_existing)
                <div class="mb-6">
                    <p class="text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('provider.profile.logo_current') }}</p>
                    <div class="flex items-center gap-3">
                        <img src="{{ $logo_url_existing }}" class="w-20 h-20 object-cover rounded-xl border border-[#D4CFC7]/60" />
                        <button type="button" wire:click="deleteLogo" class="text-sm text-[#A84936] font-medium hover:underline">{{ __('provider.profile.delete_logo') }}</button>
                    </div>
                </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.registration.logo') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.registration.logo_hint') }}</p>
                <input type="file" wire:model="logo_new" accept="image/*" class="w-full text-sm text-[#6B665E] file:me-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-[#F0D9B5]/50 file:text-[#7A5530] file:font-semibold hover:file:bg-[#F0D9B5]" />
                @error('logo_new') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            @if (count($portfolio_images_existing ?? []) > 0)
                <div class="mb-6">
                    <p class="text-sm font-semibold text-[#1A1A1A] mb-2">{{ __('provider.profile.existing_images') }}</p>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                        @foreach ($portfolio_images_existing as $i => $img)
                            <div class="relative group" wire:key="exist-img-{{ $i }}">
                                <img src="{{ $img }}" class="w-full h-24 object-cover rounded-lg border border-[#D4CFC7]/60" />
                                <button type="button" wire:click="deleteExistingImage({{ $i }})" aria-label="{{ __('provider.profile.delete_image') }}" title="{{ __('provider.profile.delete_image') }}" class="absolute top-1 end-1 w-6 h-6 rounded-full bg-[#A84936] text-white text-xs opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <span class="material-symbols-outlined text-sm" aria-hidden="true">close</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.profile.new_images') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.profile.new_images_hint') }}</p>
                <input type="file" wire:model="portfolio_images_new" multiple accept="image/*" class="w-full text-sm text-[#6B665E] file:me-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-[#F0D9B5]/50 file:text-[#7A5530] file:font-semibold hover:file:bg-[#F0D9B5]" />
                @error('portfolio_images_new.*') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>
        </section>

        <div class="pt-4 border-t border-[#D4CFC7]/30 flex justify-end">
            <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-[#C8956C] text-white font-semibold text-base hover:bg-[#b8845b] shadow-sm hover:shadow-md transition-all active:scale-[0.97] disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">{{ __('provider.profile.save') }}</span>
                <span wire:loading wire:target="save">…</span>
            </button>
        </div>
    </form>
</div>
