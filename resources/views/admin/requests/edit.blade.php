@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl">
        <nav class="text-sm text-[#9A958D] mb-3">
            <a href="{{ route('admin.requests.show', $wr->wed_uid) }}" class="inline-flex items-center gap-1 hover:text-[#C8956C] transition-colors">
                <x-z.icon name="arrow_back" class="text-base" /> {{ $wr->wed_uid }}
            </a>
        </nav>

        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A] mb-1">{{ __('Edit request') }}</h1>
        <p class="text-sm text-[#6B665E] mb-6">{{ __("Correct the couple's details or adjust the request criteria.") }}</p>

        @if ($errors->any())
            <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-100 text-[#A84936] text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        @php
            $inputClass = 'w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors';
        @endphp

        <form method="POST" action="{{ route('admin.requests.update', $wr->wed_uid) }}" class="space-y-5 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __("Couple's first name") }}</label>
                    <input type="text" name="couple_first_name" value="{{ old('couple_first_name', $wr->couple_first_name) }}" required class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('WhatsApp number') }}</label>
                    <input type="text" name="couple_whatsapp" value="{{ old('couple_whatsapp', $wr->couple_whatsapp) }}" required placeholder="+12125550123" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('City') }}</label>
                    <select name="couple_city_code" required class="{{ $inputClass }}">
                        @foreach ($cities as $w)
                            <option value="{{ $w->code }}" @selected(old('couple_city_code', $wr->couple_city_code) === $w->code)>{{ $w->code }} · {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('District') }} <span class="text-[#9A958D] font-normal">{{ __('(optional)') }}</span></label>
                    <input type="text" name="couple_district" value="{{ old('couple_district', $wr->couple_district) }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Wedding date') }}</label>
                    <input type="date" name="wedding_date" value="{{ old('wedding_date', $wr->wedding_date?->format('Y-m-d')) }}" class="{{ $inputClass }}" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Season') }}</label>
                    <select name="wedding_season" class="{{ $inputClass }}">
                        <option value="">{{ __('No season') }}</option>
                        @foreach (\App\Models\WedRequest::SEASONS as $s)
                            <option value="{{ $s }}" @selected(old('wedding_season', $wr->wedding_season) === $s)>{{ \App\Models\WedRequest::labelForSeason($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Number of guests') }}</label>
                    <select name="guest_count" class="{{ $inputClass }}">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach ($guestCounts as $g)
                            <option value="{{ $g->value }}" @selected(old('guest_count', $wr->guest_count?->value) === $g->value)>{{ $g->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Budget') }}</label>
                    <select name="budget_range" class="{{ $inputClass }}">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach ($budgetRanges as $b)
                            <option value="{{ $b->value }}" @selected(old('budget_range', $wr->budget_range?->value) === $b->value)>{{ $b->label($wr->currency()) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Description') }}</label>
                <textarea name="description" rows="3" maxlength="500" class="{{ $inputClass }} resize-none">{{ old('description', $wr->description) }}</textarea>
            </div>

            {{-- Services (sync children: added = new sub-request, removed = soft delete) --}}
            <fieldset class="pt-4 border-t border-[#D4CFC7]/40">
                <legend class="text-sm font-semibold text-[#1A1A1A] mb-1">{{ __('Providers wanted') }} <span class="text-[#C8956C]">*</span></legend>
                <p class="text-xs text-[#9A958D] mb-3">{{ __('Adding a service creates a new sub-request (status: new). Removing a service deletes its sub-request (not possible once it has been accepted).') }}</p>
                @php $currentServices = old('services_needed', (array) $wr->services_needed); @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach ($serviceTypes as $svc)
                        @php $locked = in_array($svc->value, $lockedServices, true); @endphp
                        <label @class([
                            'flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium transition-colors',
                            'cursor-not-allowed opacity-70 border-[#C8956C]/40 bg-[#F0D9B5]/30 text-[#7A5530]' => $locked,
                            'cursor-pointer border-[#D4CFC7] bg-white text-[#6B665E] hover:border-[#C8956C]/50 has-[:checked]:border-[#C8956C] has-[:checked]:bg-[#F0D9B5]/40 has-[:checked]:text-[#1A1A1A]' => ! $locked,
                        ])>
                            @if ($locked)
                                {{-- accepted child: always submitted, not editable --}}
                                <input type="hidden" name="services_needed[]" value="{{ $svc->value }}" />
                                <input type="checkbox" checked disabled class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C]" />
                            @else
                                <input type="checkbox" name="services_needed[]" value="{{ $svc->value }}"
                                       @checked(in_array($svc->value, $currentServices, true))
                                       class="w-3.5 h-3.5 rounded border-[#D4CFC7] text-[#C8956C] focus:ring-[#C8956C]/30" />
                            @endif
                            <span>{{ $svc->label() }}</span>
                            @if ($locked)<x-z.icon name="lock" class="text-xs ml-auto" />@endif
                        </label>
                    @endforeach
                </div>
                @error('services_needed') <p class="mt-2 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Status + expiration --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-[#D4CFC7]/40">
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Request status') }} <span class="text-[#C8956C]">*</span></label>
                    <select name="status" required class="{{ $inputClass }}">
                        @foreach ($statuses as $st)
                            <option value="{{ $st }}" @selected(old('status', $wr->status?->value ?? $wr->status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-[#1A1A1A] mb-1.5">{{ __('Expiration') }} <span class="text-[#9A958D] font-normal">{{ __('(empty = unchanged)') }}</span></label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $wr->expires_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }}" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.requests.show', $wr->wed_uid) }}" class="text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors">{{ __('Cancel') }}</a>
                <x-z.btn type="submit" variant="accent">{{ __('Save') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
