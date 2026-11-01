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

<div class="bg-white rounded-2xl shadow-sm border border-[#D4CFC7]/50 p-6 sm:p-10">

    {{-- Progress --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-2 text-xs">
            <span class="font-bold uppercase tracking-widest text-[#C8956C]">
                {{ __('provider.registration.step_of', ['current' => $step, 'total' => $totalSteps]) }}
            </span>
            <span class="text-[#9A958D] font-medium">{{ $steps[$step] ?? '' }}</span>
        </div>
        <div class="flex gap-1.5">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <div @class(['h-1.5 flex-1 rounded-full transition-colors', 'bg-[#C8956C]' => $i <= $step, 'bg-[#D4CFC7]/40' => $i > $step])></div>
            @endfor
        </div>
    </div>

    @error('submission')
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-[#A84936] text-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-xl shrink-0" aria-hidden="true">error</span>{{ $message }}
        </div>
    @enderror

    <form wire:submit.prevent="submit" class="space-y-5">

        {{-- STEP 1 --}}
        @if ($step === 1)
            <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-4">{{ $steps[1] }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.full_name') }}</label>
                    <input type="text" wire:model="full_name" maxlength="100" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    @error('full_name') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.auth.email') }}</label>
                    <input type="email" dir="ltr" wire:model="email" autocomplete="email" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    @error('email') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.auth.password') }}</label>
                    <input type="password" wire:model="password" autocomplete="new-password" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    {{-- Keep in step with the min:8 rule in RegistrationForm::rulesForStep(1). --}}
                    <p class="mt-1 text-xs text-[#9A958D]">{{ __('provider.auth.password_hint', ['min' => 8]) }}</p>
                    @error('password') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.auth.password_confirm') }}</label>
                    <input type="password" wire:model="password_confirmation" autocomplete="new-password" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.phone') }}</label>
                    <input type="tel" dir="ltr" wire:model.blur="phone" placeholder="+213661234567" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    <p class="mt-1 text-xs text-[#9A958D]">{!! __('provider.registration.phone_hint', ['prefix' => '<span dir="ltr">+213</span>', 'example' => '<span dir="ltr">+213661234567</span>']) !!}</p>
                    @error('phone') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.whatsapp') }}</label>
                    <input type="tel" dir="ltr" wire:model="whatsapp" placeholder="+213661234567" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                    <p class="mt-1 text-xs text-[#9A958D]">{!! __('provider.registration.phone_hint', ['prefix' => '<span dir="ltr">+213</span>', 'example' => '<span dir="ltr">+213661234567</span>']) !!}</p>
                    @error('whatsapp') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-start gap-3 p-3 rounded-xl border border-[#D4CFC7]/60 hover:border-[#C8956C]/40 transition-colors cursor-pointer has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/30">
                <input type="checkbox" wire:model="accept_terms" class="mt-0.5 w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                <span class="text-sm text-[#6B665E]">{{ __('provider.registration.accept_terms') }}</span>
            </label>
            @error('accept_terms') <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror

            <label class="flex items-start gap-3 p-3 rounded-xl border border-[#D4CFC7]/60 hover:border-[#C8956C]/40 transition-colors cursor-pointer has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/30">
                <input type="checkbox" wire:model="accept_anpdp" class="mt-0.5 w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                <span class="text-sm text-[#6B665E]">{{ __('provider.registration.accept_anpdp') }}</span>
            </label>
            @error('accept_anpdp') <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 2 --}}
        @if ($step === 2)
            <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-4">{{ $steps[2] }}</h2>
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
                    @error('business_type') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
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
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.primary_wilaya') }}</label>
                    <select wire:model.live="primary_wilaya_code" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('Sélectionner…') }}</option>
                        @foreach ($this->wilayas as $w)
                            <option value="{{ $w->code }}">{{ $w->code }} · {{ app()->getLocale() === 'ar' ? $w->name_ar : $w->name_fr }}</option>
                        @endforeach
                    </select>
                    @error('primary_wilaya_code') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('provider.registration.price_tier') }}</label>
                    <select wire:model="price_tier" class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                        <option value="">{{ __('Sélectionner…') }}</option>
                        @foreach (__('provider.registration.price_tier_options') as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('price_tier') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
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
                    @error('languages') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- STEP 3 --}}
        @if ($step === 3)
            <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">{{ $steps[3] }}</h2>
            <p class="text-sm text-[#6B665E]">{{ __('provider.registration.services_hint') }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ($this->serviceTypes as $svc)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-[#D4CFC7]/60 hover:border-[#C8956C]/40 cursor-pointer transition-colors has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/30">
                        <input type="checkbox" value="{{ $svc->value }}" wire:model="services" class="w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                        <span class="text-sm font-semibold text-[#1A1A1A]">{{ $svc->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('services') <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 4 --}}
        @if ($step === 4)
            <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">{{ $steps[4] }}</h2>
            <p class="text-sm text-[#6B665E]">{{ __('provider.registration.coverage_hint') }}</p>
            <div class="max-h-96 overflow-y-auto border border-[#D4CFC7]/60 rounded-xl divide-y divide-[#D4CFC7]/30">
                @foreach ($this->wilayas as $w)
                    @php $isPrimary = $w->code === $primary_wilaya_code; $isSelected = in_array($w->code, $coverage_wilayas, true); @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3 {{ $isSelected ? 'bg-[#F0D9B5]/20' : '' }}">
                        <label class="flex items-center gap-3 flex-1 cursor-pointer">
                            <input type="checkbox"
                                   wire:click="toggleCoverageWilaya('{{ $w->code }}')"
                                   @checked($isSelected || $isPrimary)
                                   @disabled($isPrimary)
                                   class="w-5 h-5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            <span class="text-sm font-medium text-[#1A1A1A]">{{ $w->code }} · {{ app()->getLocale() === 'ar' ? $w->name_ar : $w->name_fr }}</span>
                            @if ($isPrimary)
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[#7A5530] bg-[#F0D9B5] px-2 py-0.5 rounded-full">{{ __('provider.registration.coverage_primary_badge') }}</span>
                            @endif
                        </label>
                        @if ($isSelected && ! $isPrimary)
                            <input type="number" min="0" step="500"
                                   wire:model="travel_fees.{{ $w->code }}"
                                   placeholder="{{ __('DA') }}"
                                   aria-label="{{ __('provider.registration.coverage_travel_fee') }}"
                                   title="{{ __('provider.registration.coverage_travel_fee') }}"
                                   class="w-28 px-2 py-1.5 text-sm bg-white rounded-lg border border-[#D4CFC7] outline-none focus:border-[#C8956C] focus:ring-1 focus:ring-[#C8956C]/15"
                            />
                        @endif
                    </div>
                @endforeach
            </div>
            @error('coverage_wilayas') <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror
        @endif

        {{-- STEP 5 --}}
        @if ($step === 5)
            <h2 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-4">{{ $steps[5] }}</h2>
            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.registration.portfolio_urls_label') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.registration.portfolio_urls_hint') }}</p>
                <div class="space-y-2">
                    @foreach ($portfolio_urls as $i => $url)
                        <div wire:key="url-{{ $i }}" class="flex gap-2">
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9A958D] text-lg pointer-events-none" aria-hidden="true">link</span>
                                <input type="url" dir="ltr" wire:model="portfolio_urls.{{ $i }}" placeholder="{{ __('provider.registration.portfolio_url_placeholder') }}"
                                       class="w-full pl-10 pr-4 py-3 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all" />
                            </div>
                            @if (count($portfolio_urls) > 1)
                                <button type="button" wire:click="removePortfolioUrl({{ $i }})" aria-label="{{ __('provider.registration.remove_url') }}" title="{{ __('provider.registration.remove_url') }}" class="w-10 h-[46px] rounded-lg flex items-center justify-center shrink-0 text-[#9A958D] hover:text-[#A84936] hover:bg-red-50 transition-colors">
                                    <span class="material-symbols-outlined text-xl" aria-hidden="true">remove_circle_outline</span>
                                </button>
                            @endif
                        </div>
                        @error('portfolio_urls.' . $i) <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    @endforeach
                </div>
                @if (count($portfolio_urls) < 5)
                    <button type="button" wire:click="addPortfolioUrl" class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-[#C8956C] hover:text-[#b8845b] transition-colors">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">add_circle</span>{{ __('provider.registration.add_url') }}
                    </button>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.registration.portfolio_images') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.registration.portfolio_images_hint') }}</p>
                <input type="file" wire:model="portfolio_images" multiple accept="image/*" class="w-full text-sm text-[#6B665E] file:me-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-[#F0D9B5]/50 file:text-[#7A5530] file:font-semibold hover:file:bg-[#F0D9B5]" />
                <div wire:loading wire:target="portfolio_images" class="text-xs text-[#C8956C] mt-2">{{ __('provider.registration.uploading') }}</div>
                @error('portfolio_images.*') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror

                @if (count($portfolio_images ?? []) > 0)
                    <div class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach ($portfolio_images as $img)
                            @if ($img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                <img src="{{ $img->temporaryUrl() }}" class="w-full h-20 object-cover rounded-lg border border-[#D4CFC7]/60" />
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('provider.registration.logo') }}</label>
                <p class="mb-2 text-xs text-[#9A958D]">{{ __('provider.registration.logo_hint') }}</p>
                <input type="file" wire:model="logo_upload" accept="image/*" class="w-full text-sm text-[#6B665E] file:me-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-[#F0D9B5]/50 file:text-[#7A5530] file:font-semibold hover:file:bg-[#F0D9B5]" />
                @error('logo_upload') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                @if ($logo_upload instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                    <img src="{{ $logo_upload->temporaryUrl() }}" class="mt-3 w-24 h-24 object-cover rounded-xl border border-[#D4CFC7]/60" />
                @endif
            </div>
        @endif

        {{-- Navigation --}}
        <div class="flex items-center justify-between pt-6 border-t border-[#D4CFC7]/30">
            <button type="button" wire:click="previousStep"
                    @class(['inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors', 'text-[#1A1A1A] hover:bg-[#F7F4F0]' => $step > 1, 'invisible' => $step === 1])>
                <span class="material-symbols-outlined text-lg rtl:-scale-x-100" aria-hidden="true">arrow_back</span>{{ __('provider.registration.previous') }}
            </button>

            @if ($step < $totalSteps)
                <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-[#1A1A1A] text-white font-semibold text-sm hover:bg-black shadow-sm hover:shadow-md transition-all active:scale-[0.97] disabled:opacity-60 disabled:cursor-not-allowed">
                    {{ __('provider.registration.next') }}<span class="material-symbols-outlined text-lg rtl:-scale-x-100" aria-hidden="true">arrow_forward</span>
                </button>
            @else
                <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-[#C8956C] text-white font-semibold text-sm hover:bg-[#b8845b] shadow-sm hover:shadow-md transition-all active:scale-[0.97] disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="submit">{{ __('provider.registration.submit') }}</span>
                    <span wire:loading wire:target="submit">…</span>
                </button>
            @endif
        </div>
    </form>
</div>
