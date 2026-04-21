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
@endphp

<div class="bg-white rounded-2xl shadow-lg shadow-emerald-900/5 border border-emerald-50 p-6 sm:p-10">

    {{-- Progress indicator --}}
    <div class="mb-8">
        <div class="flex justify-between items-center mb-3">
            <span class="text-xs font-medium uppercase tracking-widest text-brand">
                {{ __('form.step_of', ['current' => $step, 'total' => $totalSteps]) }}
            </span>
            <span class="text-xs text-gray-400">{{ round(($step / $totalSteps) * 100) }}%</span>
        </div>
        <div class="flex gap-1.5">
            @for ($i = 1; $i <= $totalSteps; $i++)
                <button
                    type="button"
                    wire:click="goToStep({{ $i }})"
                    @class([
                        'h-1.5 flex-1 rounded-full transition-colors',
                        'bg-brand' => $i <= $step,
                        'bg-emerald-100' => $i > $step,
                        'cursor-pointer hover:bg-brand' => $i < $step,
                        'cursor-default' => $i >= $step,
                    ])
                    @disabled($i >= $step)
                    aria-label="Étape {{ $i }}"
                ></button>
            @endfor
        </div>
    </div>

    {{-- Step heading --}}
    <div class="mb-8">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
            {{ $stepTitles[$step] ?? '' }}
        </h1>
        <p class="text-sm text-gray-500 mt-2">{{ $stepIntros[$step] ?? '' }}</p>
    </div>

    {{-- Submission error --}}
    @error('submission')
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit.prevent="submit" class="space-y-6" novalidate>

        {{-- STEP 1 --}}
        @if ($step === 1)
            <div>
                <label for="couple_first_name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_first_name') }}
                </label>
                <input
                    type="text"
                    id="couple_first_name"
                    wire:model="couple_first_name"
                    placeholder="{{ __('form.placeholder_first_name') }}"
                    maxlength="50"
                    autocomplete="given-name"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                    required
                />
                @error('couple_first_name') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="couple_whatsapp" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_whatsapp') }}
                </label>
                <input
                    type="tel"
                    id="couple_whatsapp"
                    wire:model="couple_whatsapp"
                    placeholder="{{ __('form.placeholder_whatsapp') }}"
                    autocomplete="tel"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition"
                    required
                />
                <p class="mt-2 text-xs text-gray-500">{{ __('form.help_whatsapp') }}</p>
                @error('couple_whatsapp') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- STEP 2 --}}
        @if ($step === 2)
            <div>
                <label for="couple_wilaya_code" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_wilaya') }}
                </label>
                <select
                    id="couple_wilaya_code"
                    wire:model.live="couple_wilaya_code"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white"
                    required
                >
                    <option value="">{{ __('form.placeholder_wilaya') }}</option>
                    @foreach ($this->wilayas as $w)
                        <option value="{{ $w->code }}">{{ $w->code }} — {{ $w->name_fr }}</option>
                    @endforeach
                </select>
                @error('couple_wilaya_code') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="couple_commune" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_commune') }} <span class="text-gray-400 font-normal">(facultatif)</span>
                </label>
                <select
                    id="couple_commune"
                    wire:model="couple_commune"
                    @disabled($couple_wilaya_code === '')
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white disabled:bg-gray-50 disabled:text-gray-400"
                >
                    <option value="">{{ __('form.placeholder_commune') }}</option>
                    @foreach ($this->availableCommunes as $c)
                        <option value="{{ $c->name_fr }}">{{ $c->name_fr }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">{{ __('form.help_commune') }}</p>
                @error('couple_commune') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- STEP 3 --}}
        @if ($step === 3)
            <div>
                <label for="wedding_date" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_wedding_date') }} <span class="text-gray-400 font-normal">(facultatif)</span>
                </label>
                <input
                    type="date"
                    id="wedding_date"
                    wire:model.live="wedding_date"
                    min="{{ now()->toDateString() }}"
                    max="{{ now()->addYears(2)->toDateString() }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white"
                />
                @error('wedding_date') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="wedding_season" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_wedding_season') }} <span class="text-gray-400 font-normal">(si pas de date)</span>
                </label>
                <select
                    id="wedding_season"
                    wire:model="wedding_season"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white"
                >
                    <option value="">{{ __('form.season_placeholder') }}</option>
                    <option value="Printemps">{{ __('form.season_printemps') }}</option>
                    <option value="Été">{{ __('form.season_ete') }}</option>
                    <option value="Automne">{{ __('form.season_automne') }}</option>
                    <option value="Hiver">{{ __('form.season_hiver') }}</option>
                </select>
                <p class="mt-2 text-xs text-gray-500">{{ __('form.help_date_or_season') }}</p>
                @error('wedding_season') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="guest_count" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_guest_count') }}
                </label>
                <select
                    id="guest_count"
                    wire:model="guest_count"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white"
                    required
                >
                    <option value="">{{ __('form.placeholder_guest_count') }}</option>
                    @foreach ($this->guestCounts as $g)
                        <option value="{{ $g->value }}">{{ $g->label() }}</option>
                    @endforeach
                </select>
                @error('guest_count') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- STEP 4 --}}
        @if ($step === 4)
            <div>
                <label for="budget_range" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_budget') }}
                </label>
                <select
                    id="budget_range"
                    wire:model="budget_range"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition bg-white"
                    required
                >
                    <option value="">{{ __('form.placeholder_budget') }}</option>
                    @foreach ($this->budgetRanges as $b)
                        <option value="{{ $b->value }}">{{ $b->label() }}</option>
                    @endforeach
                </select>
                @error('budget_range') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <fieldset>
                <legend class="block text-sm font-medium text-gray-700 mb-3">{{ __('form.label_services') }}</legend>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($this->serviceTypes as $svc)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 hover:border-brand cursor-pointer transition has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50">
                            <input
                                type="checkbox"
                                value="{{ $svc->value }}"
                                wire:model="services_needed"
                                class="w-5 h-5 rounded text-brand focus:ring-2 focus:ring-emerald-100"
                            />
                            <span class="text-sm font-medium text-gray-800">{{ $svc->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('services_needed') <p class="mt-3 text-xs text-red-600">{{ $message }}</p> @enderror
            </fieldset>
        @endif

        {{-- STEP 5 --}}
        @if ($step === 5)
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('form.label_description') }}
                </label>
                <textarea
                    id="description"
                    wire:model.live="description"
                    rows="4"
                    maxlength="500"
                    placeholder="{{ __('form.placeholder_description') }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-brand focus:ring-2 focus:ring-emerald-100 focus:outline-none transition resize-none"
                ></textarea>
                <p class="mt-2 text-xs text-gray-400 text-right">
                    {{ __('form.help_char_count', ['count' => mb_strlen($description), 'max' => 500]) }}
                </p>
                @error('description') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Review card --}}
            @php
                $guestLabel = $guest_count ? \App\Enums\GuestCount::from($guest_count)->label() : __('form.review_none');
                $budgetLabel = $budget_range ? \App\Enums\BudgetRange::from($budget_range)->label() : __('form.review_none');
                $serviceLabels = collect($services_needed)->map(fn ($v) => \App\Enums\ServiceType::from($v)->label())->all();
                $wilayaRow = \App\Models\Wilaya::where('code', $couple_wilaya_code)->first();
            @endphp
            <section class="rounded-xl bg-emerald-50/60 border border-emerald-100 p-5 space-y-3 text-sm">
                <h2 class="font-display text-lg font-semibold text-brand">{{ __('form.review_title') }}</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                    <div><span class="text-gray-500">{{ __('form.review_couple') }} :</span> <strong>{{ $couple_first_name ?: __('form.review_none') }}</strong> ({{ $couple_whatsapp ?: '—' }})</div>
                    <div><span class="text-gray-500">{{ __('form.review_location') }} :</span> <strong>{{ $wilayaRow?->name_fr ?? __('form.review_none') }}</strong>{{ $couple_commune ? ', ' . $couple_commune : '' }}</div>
                    <div>
                        <span class="text-gray-500">{{ __('form.review_wedding') }} :</span>
                        @if ($wedding_date)
                            <strong>{{ \Illuminate\Support\Carbon::parse($wedding_date)->isoFormat('LL') }}</strong>
                        @elseif ($wedding_season)
                            <strong>{{ $wedding_season }}</strong>
                        @else
                            <strong>{{ __('form.review_none') }}</strong>
                        @endif
                        · {{ $guestLabel }}
                    </div>
                    <div><span class="text-gray-500">{{ __('form.review_budget') }} :</span> <strong>{{ $budgetLabel }}</strong></div>
                    <div class="sm:col-span-2"><span class="text-gray-500">{{ __('form.review_services') }} :</span> <strong>{{ $serviceLabels ? implode(', ', $serviceLabels) : __('form.review_none') }}</strong></div>
                    @if ($description)
                        <div class="sm:col-span-2"><span class="text-gray-500">{{ __('form.review_notes') }} :</span> <em class="text-gray-700">« {{ $description }} »</em></div>
                    @endif
                </div>
            </section>

            <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-emerald-50/50 transition">
                <input
                    type="checkbox"
                    wire:model="acceptTerms"
                    class="mt-0.5 w-5 h-5 rounded text-brand focus:ring-2 focus:ring-emerald-100"
                    required
                />
                <span class="text-xs sm:text-sm text-gray-700 leading-relaxed">{{ __('form.label_accept_terms') }}</span>
            </label>
            @error('acceptTerms') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        @endif

        {{-- Navigation buttons --}}
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <button
                type="button"
                wire:click="previousStep"
                @class([
                    'px-5 py-2.5 rounded-xl text-sm font-medium transition',
                    'text-gray-600 hover:text-brand hover:bg-emerald-50' => $step > 1,
                    'invisible' => $step === 1,
                ])
            >
                {{ __('form.btn_previous') }}
            </button>

            @if ($step < $totalSteps)
                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    class="px-6 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="nextStep">{{ __('form.btn_next') }}</span>
                    <span wire:loading wire:target="nextStep">…</span>
                </button>
            @else
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="px-6 py-3 rounded-xl bg-brand hover:bg-brand-dark text-white font-semibold text-sm shadow-md shadow-emerald-900/10 transition disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="submit">{{ __('form.btn_submit') }}</span>
                    <span wire:loading wire:target="submit">Envoi en cours…</span>
                </button>
            @endif
        </div>
    </form>
</div>
