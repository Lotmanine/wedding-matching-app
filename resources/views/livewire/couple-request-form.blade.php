@php
    $totalSteps = 5;
    $stepTitles = [
        1 => __('form.step_1_title'),
        2 => __('form.step_2_title'),
        3 => __('form.step_3_title'),
        4 => __('form.step_4_title'),
        5 => __('form.step_5_title'),
    ];
    $stepIntros = [
        1 => __('form.step_1_intro'),
        2 => __('form.step_2_intro'),
        3 => __('form.step_3_intro'),
        4 => __('form.step_4_intro'),
        5 => __('form.step_5_intro'),
    ];
    $stepMeta = [
        1 => ['label' => __('form.step_1_title'), 'icon' => 'person'],
        2 => ['label' => __('form.step_2_title'), 'icon' => 'location_on'],
        3 => ['label' => __('form.step_3_title'), 'icon' => 'checklist'],
        4 => ['label' => __('form.step_4_title'), 'icon' => 'calendar_month'],
        5 => ['label' => __('form.step_5_title'), 'icon' => 'edit_note'],
    ];

    // Shared input class (design-system spec)
    $inputClass = 'w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15';
@endphp

<div class="flex flex-col lg:flex-row gap-10 lg:gap-14 items-start">

    {{-- ─── Left rail : parcours + récapitulatif ───────────────────── --}}
    <aside class="hidden lg:block w-72 shrink-0">
        <div class="sticky top-28">
            <p class="text-[11px] font-bold uppercase tracking-widest text-[#C8956C] mb-6">{{ __('form.sidebar_journey') }}</p>
            <nav class="space-y-2">
                @foreach ($stepMeta as $i => $meta)
                    @php $done = $i < $step; $active = $i === $step; @endphp
                    <button
                        type="button"
                        @if ($i < $step) wire:click="goToStep({{ $i }})" @endif
                        @class([
                            'w-full text-start flex items-center gap-3 px-4 py-3 rounded-xl transition-all',
                            'bg-[#F0D9B5]/50 border border-[#C8956C]/30' => $active,
                            'opacity-60 hover:opacity-100 cursor-pointer' => $done,
                            'opacity-40 cursor-default' => $i > $step,
                        ])
                        @disabled($i >= $step)
                    >
                        <span @class([
                            'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0',
                            'bg-[#1A1A1A] text-white' => $done,
                            'bg-[#C8956C] text-white' => $active,
                            'bg-[#D4CFC7] text-[#9A958D]' => $i > $step,
                        ])>
                            @if ($done)
                                <x-z.icon name="check" class="text-sm" />
                            @else
                                {{ $i }}
                            @endif
                        </span>
                        <span @class([
                            'text-sm',
                            'text-[#1A1A1A] font-semibold' => $active,
                            'text-[#6B665E] font-medium' => ! $active,
                        ])>{{ $meta['label'] }}</span>
                    </button>
                @endforeach
            </nav>

            {{-- Récapitulatif live --}}
            @if ($couple_first_name || $couple_wilaya_code || count($services_needed))
                @php $wilayaSummary = $couple_wilaya_code ? \App\Models\Wilaya::where('code', $couple_wilaya_code)->first() : null; @endphp
                <div class="mt-8 p-4 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-[#9A958D] mb-3">{{ __('form.review_title') }}</p>
                    <div class="space-y-1.5 text-sm">
                        @if ($couple_first_name)
                            <p class="text-[#1A1A1A] font-medium">{{ $couple_first_name }}</p>
                        @endif
                        @if ($wilayaSummary)
                            <p class="text-[#6B665E] flex items-center gap-1"><x-z.icon name="location_on" class="text-sm text-[#C8956C]" />{{ app()->getLocale() === 'ar' ? $wilayaSummary->name_ar : $wilayaSummary->name_fr }}</p>
                        @endif
                        @if (count($services_needed))
                            <p class="text-[#6B665E] flex items-center gap-1"><x-z.icon name="checklist" class="text-sm text-[#C8956C]" />{{ trans_choice('form.services_count', count($services_needed), ['count' => count($services_needed)]) }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </aside>

    {{-- ─── Form card ──────────────────────────────────────────────── --}}
    <div class="flex-1 w-full max-w-xl">

        {{-- Mobile step indicator --}}
        <div class="lg:hidden mb-7 flex items-center justify-between">
            <div class="flex items-center gap-0">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    @php $done = $i < $step; $active = $i === $step; @endphp
                    <button
                        type="button"
                        @if ($i < $step) wire:click="goToStep({{ $i }})" @endif
                        @disabled($i >= $step)
                        aria-label="{{ __('form.step_of', ['current' => $i, 'total' => $totalSteps]) }}"
                        @class([
                            'flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all shrink-0',
                            'bg-[#C8956C] text-white shadow-md shadow-[#C8956C]/30' => $active,
                            'bg-[#1A1A1A] text-white cursor-pointer' => $done,
                            'bg-[#F7F4F0] text-[#9A958D] cursor-default' => $i > $step,
                        ])
                    >
                        @if ($done)
                            <x-z.icon name="check" class="text-sm" />
                        @else
                            {{ $i }}
                        @endif
                    </button>
                    @if ($i < $totalSteps)
                        <div @class([
                            'h-0.5 w-5 sm:w-8 transition-all',
                            'bg-[#1A1A1A]' => $done,
                            'bg-[#D4CFC7]' => ! $done,
                        ])></div>
                    @endif
                @endfor
            </div>
            <span class="text-xs text-[#9A958D] font-medium">{{ $stepMeta[$step]['label'] }}</span>
        </div>

        {{-- Progress bar (gradient) --}}
        <div class="h-1 rounded-full bg-[#D4CFC7]/30 mb-8 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-[#C8956C] to-[#F0D9B5] transition-all duration-500" style="width: {{ round(($step / $totalSteps) * 100) }}%"></div>
        </div>

        {{-- Submission error --}}
        @error('submission')
            <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-[#A84936]/8 border border-[#A84936]/25 text-[#A84936] text-sm">
                <x-z.icon name="error" :filled="true" class="text-lg shrink-0" />
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-7 md:p-10">

            {{-- Step heading --}}
            <div class="mb-2">
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-[#1A1A1A] leading-tight">
                    {{ $stepTitles[$step] ?? '' }}
                </h1>
                <p class="text-[#6B665E] mt-2">{{ $stepIntros[$step] ?? '' }}</p>
            </div>

            <div class="h-px bg-[#D4CFC7]/40 my-7"></div>

            <form wire:submit.prevent="submit" class="space-y-6" novalidate>

                {{-- STEP 1 — Coordonnées --}}
                @if ($step === 1)
                    <div class="space-y-6">
                        <div>
                            <label for="couple_first_name" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_first_name') }} <span class="text-[#C8956C]">*</span>
                            </label>
                            <input
                                type="text"
                                id="couple_first_name"
                                wire:model="couple_first_name"
                                placeholder="{{ __('form.placeholder_first_name') }}"
                                maxlength="50"
                                autocomplete="given-name"
                                class="{{ $inputClass }}"
                                required
                            />
                            @error('couple_first_name') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="couple_whatsapp" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_whatsapp') }} <span class="text-[#C8956C]">*</span>
                            </label>
                            <input
                                type="tel"
                                id="couple_whatsapp"
                                wire:model="couple_whatsapp"
                                placeholder="{{ __('form.placeholder_whatsapp') }}"
                                autocomplete="tel"
                                dir="ltr"
                                class="{{ $inputClass }} rtl:text-right"
                                required
                            />
                            <p class="mt-2 text-xs text-[#9A958D]">{{ __('form.help_whatsapp') }}</p>
                            @error('couple_whatsapp') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 2 — Localisation --}}
                @if ($step === 2)
                    <div class="space-y-6">
                        <div>
                            <label for="couple_wilaya_code" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_wilaya') }} <span class="text-[#C8956C]">*</span>
                            </label>
                            <select
                                id="couple_wilaya_code"
                                wire:model.live="couple_wilaya_code"
                                class="{{ $inputClass }} appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20fill=%22none%22%20viewBox=%220%200%2024%2024%22%20stroke=%22%239A958D%22%20stroke-width=%222%22%3E%3Cpath%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22%20d=%22M19%209l-7%207-7-7%22/%3E%3C/svg%3E')] bg-[length:1.25rem] ltr:bg-[right_0.9rem_center] rtl:bg-[left_0.9rem_center] bg-no-repeat pe-11"
                                required
                            >
                                <option value="">{{ __('form.placeholder_wilaya') }}</option>
                                @foreach ($this->wilayas as $w)
                                    <option value="{{ $w->code }}">{{ $w->code }} · {{ app()->getLocale() === 'ar' ? $w->name_ar : $w->name_fr }}</option>
                                @endforeach
                            </select>
                            @error('couple_wilaya_code') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="couple_commune" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_commune') }} <span class="text-[#9A958D] font-normal">{{ __('(facultatif)') }}</span>
                            </label>
                            <select
                                id="couple_commune"
                                wire:model="couple_commune"
                                @disabled($couple_wilaya_code === '')
                                class="{{ $inputClass }} appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20fill=%22none%22%20viewBox=%220%200%2024%2024%22%20stroke=%22%239A958D%22%20stroke-width=%222%22%3E%3Cpath%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22%20d=%22M19%209l-7%207-7-7%22/%3E%3C/svg%3E')] bg-[length:1.25rem] ltr:bg-[right_0.9rem_center] rtl:bg-[left_0.9rem_center] bg-no-repeat pe-11 disabled:bg-[#F7F4F0] disabled:text-[#9A958D] disabled:cursor-not-allowed"
                            >
                                <option value="">{{ __('form.placeholder_commune') }}</option>
                                @foreach ($this->availableCommunes as $c)
                                    <option value="{{ $c->name_fr }}">{{ app()->getLocale() === 'ar' ? $c->name_ar : $c->name_fr }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-[#9A958D]">{{ __('form.help_commune') }}</p>
                            @error('couple_commune') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- STEP 4 — Date / saison / invités (conditionnel) --}}
                @if ($step === 4)
                    <div class="space-y-6">
                        <div>
                            <label for="wedding_date" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_wedding_date') }} <span class="text-[#C8956C]">*</span>
                            </label>
                            <input
                                type="date"
                                id="wedding_date"
                                wire:model.live="wedding_date"
                                min="{{ now()->toDateString() }}"
                                max="{{ now()->addYears(2)->toDateString() }}"
                                required
                                class="{{ $inputClass }}"
                            />
                            @error('wedding_date') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="wedding_season" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_wedding_season') }} <span class="text-[#9A958D] font-normal">{{ __('(facultatif)') }}</span>
                            </label>
                            <select
                                id="wedding_season"
                                wire:model="wedding_season"
                                class="{{ $inputClass }} appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20fill=%22none%22%20viewBox=%220%200%2024%2024%22%20stroke=%22%239A958D%22%20stroke-width=%222%22%3E%3Cpath%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22%20d=%22M19%209l-7%207-7-7%22/%3E%3C/svg%3E')] bg-[length:1.25rem] ltr:bg-[right_0.9rem_center] rtl:bg-[left_0.9rem_center] bg-no-repeat pe-11"
                            >
                                <option value="">{{ __('form.season_placeholder') }}</option>
                                <option value="Printemps">{{ __('form.season_printemps') }}</option>
                                <option value="Été">{{ __('form.season_ete') }}</option>
                                <option value="Automne">{{ __('form.season_automne') }}</option>
                                <option value="Hiver">{{ __('form.season_hiver') }}</option>
                            </select>
                            @error('wedding_season') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Guest count only when a headcount-dependent service (traiteur / salle) is chosen --}}
                        @if ($this->needsGuestCount())
                            <div>
                                <label for="guest_count" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                    {{ __('form.label_guest_count') }} <span class="text-[#C8956C]">*</span>
                                </label>
                                <select
                                    id="guest_count"
                                    wire:model="guest_count"
                                    class="{{ $inputClass }} appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20fill=%22none%22%20viewBox=%220%200%2024%2024%22%20stroke=%22%239A958D%22%20stroke-width=%222%22%3E%3Cpath%20stroke-linecap=%22round%22%20stroke-linejoin=%22round%22%20d=%22M19%209l-7%207-7-7%22/%3E%3C/svg%3E')] bg-[length:1.25rem] ltr:bg-[right_0.9rem_center] rtl:bg-[left_0.9rem_center] bg-no-repeat pe-11"
                                    required
                                >
                                    <option value="">{{ __('form.placeholder_guest_count') }}</option>
                                    @foreach ($this->guestCounts as $g)
                                        <option value="{{ $g->value }}">{{ $g->label() }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-[#9A958D]">{{ __('form.help_guest_count') }}</p>
                                @error('guest_count') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    </div>
                @endif

                {{-- STEP 3 — Budget (cartes) + services (chips) --}}
                @if ($step === 3)
                    <div class="space-y-7">
                        {{-- Budget cards --}}
                        <div>
                            <p class="text-sm font-semibold text-[#1A1A1A] mb-3">{{ __('form.label_budget') }} <span class="text-[#C8956C]">*</span></p>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ($this->budgetRanges as $b)
                                    {{-- Highlight is driven by the native :checked state, so selection is
                                         instant. `name` makes the radios a real DOM group (the browser
                                         unchecks the previous one immediately); the inner dot/check use a
                                         named group-has variant because peer-checked can't reach elements
                                         nested inside a sibling wrapper. --}}
                                    <label class="group/budget relative block text-start p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 active:scale-[0.98] border-[#D4CFC7] bg-white hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:shadow-sm">
                                        <input type="radio" name="budget_range" wire:model.live="budget_range" value="{{ $b->value }}" class="sr-only" />
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="font-semibold text-sm text-[#6B665E] group-has-[:checked]/budget:text-[#1A1A1A]">{{ $b->label() }}</p>
                                            <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5 transition-all border-[#D4CFC7] group-has-[:checked]/budget:border-[#C8956C] group-has-[:checked]/budget:bg-[#C8956C]">
                                                <x-z.icon name="check" class="text-white text-xs opacity-0 group-has-[:checked]/budget:opacity-100" />
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('budget_range') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Services chips --}}
                        <fieldset>
                            <legend class="text-sm font-semibold text-[#1A1A1A] mb-3">{{ __('form.label_services') }} <span class="text-[#C8956C]">*</span></legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($this->serviceTypes as $svc)
                                    {{-- Instant highlight via native :checked (peer). wire:model.live keeps
                                         guest-count conditional logic + review in sync on each toggle. --}}
                                    <label class="flex items-center gap-2.5 px-4 py-3 rounded-xl border-2 text-sm font-semibold cursor-pointer transition-all duration-200 active:scale-[0.97] border-[#D4CFC7] bg-white text-[#6B665E] hover:border-[#C8956C]/50 hover:bg-[#F7F4F0] has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/50 has-[:checked]:text-[#1A1A1A] has-[:checked]:shadow-sm">
                                        <input type="checkbox" value="{{ $svc->value }}" wire:model.live="services_needed" class="peer sr-only" />
                                        <span>{{ $svc->label() }}</span>
                                        <x-z.icon name="check_circle" :filled="true" class="text-[#C8956C] text-base ms-auto opacity-0 peer-checked:opacity-100" />
                                    </label>
                                @endforeach
                            </div>
                            @if (count($services_needed))
                                <p class="text-xs text-[#C8956C] mt-2 font-medium">{{ trans_choice('form.services_count', count($services_needed), ['count' => count($services_needed)]) }}</p>
                            @endif
                            @error('services_needed') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </fieldset>
                    </div>
                @endif

                {{-- STEP 5 — Description + récap + consentement --}}
                @if ($step === 5)
                    <div class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                {{ __('form.label_description') }}
                            </label>
                            <textarea
                                id="description"
                                wire:model.live="description"
                                rows="5"
                                maxlength="500"
                                placeholder="{{ __('form.placeholder_description') }}"
                                class="{{ $inputClass }} resize-none"
                            ></textarea>
                            <p class="mt-2 text-xs text-[#9A958D] text-end">
                                {{ __('form.help_char_count', ['count' => mb_strlen($description), 'max' => 500]) }}
                            </p>
                            @error('description') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
                        </div>

                        {{-- Review card --}}
                        @php
                            $guestLabel = $guest_count ? \App\Enums\GuestCount::from($guest_count)->label() : __('form.review_none');
                            $budgetLabel = $budget_range ? \App\Enums\BudgetRange::from($budget_range)->label() : __('form.review_none');
                            $serviceLabels = collect($services_needed)->map(fn ($v) => \App\Enums\ServiceType::from($v)->label())->all();
                            $wilayaRow = \App\Models\Wilaya::where('code', $couple_wilaya_code)->first();
                            $isAr = app()->getLocale() === 'ar';
                            $communeLabel = $isAr && $couple_commune
                                ? ($this->availableCommunes->firstWhere('name_fr', $couple_commune)?->name_ar ?: $couple_commune)
                                : $couple_commune;
                        @endphp
                        <section class="rounded-xl bg-[#F7F4F0] border border-[#D4CFC7]/50 p-5 space-y-3 text-sm">
                            <h2 class="font-serif text-lg font-semibold text-[#7A5530]">{{ __('form.review_title') }}</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                                <div><span class="text-[#9A958D]">{{ __('form.review_couple') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span> <strong class="text-[#1A1A1A]">{{ $couple_first_name ?: __('form.review_none') }}</strong> <span class="text-[#6B665E]" dir="ltr">({{ $couple_whatsapp ?: __('Non renseigné') }})</span></div>
                                <div><span class="text-[#9A958D]">{{ __('form.review_location') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span> <strong class="text-[#1A1A1A]">{{ ($isAr ? $wilayaRow?->name_ar : $wilayaRow?->name_fr) ?? __('form.review_none') }}</strong>{{ $couple_commune ? __(', ') . $communeLabel : '' }}</div>
                                <div>
                                    <span class="text-[#9A958D]">{{ __('form.review_wedding') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span>
                                    @if ($wedding_date)
                                        <strong class="text-[#1A1A1A]">{{ \Illuminate\Support\Carbon::parse($wedding_date)->isoFormat('LL') }}</strong>
                                    @elseif ($wedding_season)
                                        <strong class="text-[#1A1A1A]">{{ __($wedding_season) }}</strong>
                                    @else
                                        <strong class="text-[#1A1A1A]">{{ __('form.review_none') }}</strong>
                                    @endif
                                    <span class="text-[#6B665E]">· {{ $guestLabel }}</span>
                                </div>
                                <div><span class="text-[#9A958D]">{{ __('form.review_budget') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span> <strong class="text-[#1A1A1A]">{{ $budgetLabel }}</strong></div>
                                <div class="sm:col-span-2"><span class="text-[#9A958D]">{{ __('form.review_services') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span> <strong class="text-[#1A1A1A]">{{ $serviceLabels ? implode(__(', '), $serviceLabels) : __('form.review_none') }}</strong></div>
                                @if ($description)
                                    <div class="sm:col-span-2"><span class="text-[#9A958D]">{{ __('form.review_notes') }}{{ app()->getLocale() === 'ar' ? ':' : ' :' }}</span> <em class="text-[#6B665E]">« {{ $description }} »</em></div>
                                @endif
                            </div>
                        </section>

                        {{-- ANPDP consent --}}
                        <label @class([
                            'flex items-start gap-3 rounded-xl border p-5 cursor-pointer transition',
                            'border-[#C8956C] bg-[#F0D9B5]/40' => $acceptTerms,
                            'border-[#D4CFC7]/50 bg-[#F7F4F0]' => ! $acceptTerms,
                        ])>
                            <input
                                type="checkbox"
                                wire:model="acceptTerms"
                                class="mt-0.5 w-4 h-4 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/30 cursor-pointer"
                                required
                            />
                            {{-- The sentence is static copy from lang/; only the link label is escaped here. --}}
                            <span class="text-xs sm:text-sm text-[#6B665E] leading-relaxed">{!! __('form.label_accept_terms', ['link' => '<a href="' . e(route('legal.privacy')) . '" target="_blank" rel="noopener" class="font-semibold text-[#1A1A1A]" style="text-decoration:underline">' . e(__('form.accept_terms_link')) . '</a>']) !!}</span>
                        </label>
                        @error('acceptTerms') <p class="text-xs text-[#A84936]">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Navigation buttons --}}
                <div class="flex items-center justify-between mt-10 pt-6 border-t border-[#D4CFC7]/40">
                    @if ($step > 1)
                        <x-z.btn type="button" variant="ghost" wire:click="previousStep" icon="arrow_back">
                            {{ __('form.btn_previous') }}
                        </x-z.btn>
                    @else
                        <div></div>
                    @endif

                    @if ($step < $totalSteps)
                        <x-z.btn type="button" variant="primary" size="lg" wire:click="nextStep" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="nextStep">{{ __('form.btn_next') }}</span>
                            <span wire:loading wire:target="nextStep">…</span>
                            <x-z.icon name="arrow_forward" class="text-lg rtl:-scale-x-100" wire:loading.remove wire:target="nextStep" />
                        </x-z.btn>
                    @else
                        <x-z.btn type="submit" variant="accent" size="lg" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit">{{ __('form.btn_submit') }}</span>
                            <span wire:loading wire:target="submit">{{ __('Envoi en cours…') }}</span>
                            <x-z.icon name="send" class="text-lg" wire:loading.remove wire:target="submit" />
                        </x-z.btn>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
