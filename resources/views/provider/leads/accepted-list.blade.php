{{--
    Accepted requests: leads this provider unlocked. Couple contact details are
    masked once the access window (retention_days, 14 days by default) after
    acceptance has passed.
--}}
@extends('layouts.provider')

@section('content')
    <div class="max-w-5xl mx-auto px-5 md:px-8 py-8 md:py-10">

        <div class="mb-7">
            <h1 class="font-serif text-2xl md:text-3xl font-bold text-[#1A1A1A]">{{ __('Accepted requests') }}</h1>
            <p class="text-sm text-[#6B665E] mt-1">{{ __("A couple's contact details stay visible for :days days after you accept, then they are hidden.", ['days' => (int) config('wedding.privacy.retention_days', 14)]) }}</p>
        </div>

        @if ($leads->isEmpty())
            <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-12 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-[#F0D9B5]/50 flex items-center justify-center mb-4">
                    <x-z.icon name="workspace_premium" class="text-[#C8956C] text-3xl" />
                </div>
                <p class="font-serif text-lg font-bold text-[#1A1A1A]">{{ __('No accepted requests yet') }}</p>
                <p class="text-sm text-[#6B665E] mt-1">{{ __('Requests you accept will appear here.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($leads as $lead)
                    @php
                        $wr = $lead->wedRequest;
                        $anonymized = $lead->isAnonymized();
                        $anonymizeAt = $lead->anonymizeAt();
                        $cityLabel = \App\Models\City::where('code', (string) $wr?->couple_city_code)->value('name') ?? $wr?->couple_city_code;
                    @endphp
                    <a href="{{ route('provider.leads.show', $lead->service_uid) }}"
                       class="block bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-5 hover:border-[#C8956C]/50 hover:shadow-md transition-all">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-[#F0D9B5]/60 flex items-center justify-center shrink-0">
                                    <x-z.icon name="check_circle" :filled="true" class="text-[#C8956C] text-xl" />
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-[#1A1A1A] truncate">{{ $lead->service_type?->label() }}</p>
                                    <p class="text-xs text-[#9A958D]">{{ __('Accepted :when', ['when' => $lead->unlocked_at?->diffForHumans()]) }}</p>
                                </div>
                            </div>
                            @if ($anonymized)
                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-[#F7F4F0] text-[#9A958D] border border-[#D4CFC7]/60">
                                    <x-z.icon name="visibility_off" class="text-xs" /> {{ __('Access expired') }}
                                </span>
                            @else
                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full bg-[#F0D9B5]/50 text-[#7A5530] border border-[#C8956C]/30"
                                      title="{{ __('Contact details shown until :date', ['date' => $anonymizeAt?->isoFormat('LL')]) }}">
                                    <x-z.icon name="schedule" class="text-xs" /> {{ __('Access until :date', ['date' => $anonymizeAt?->isoFormat('ll')]) }}
                                </span>
                            @endif
                        </div>

                        <div class="space-y-1.5 text-sm">
                            <p class="flex items-center gap-1.5 text-[#6B665E]">
                                <x-z.icon name="person" class="text-[#C8956C] text-base" />
                                @if ($anonymized)
                                    <span class="italic text-[#9A958D]">{{ __('Not available') }}</span>
                                @else
                                    <span class="font-medium text-[#1A1A1A]">{{ $wr?->couple_first_name }}</span>
                                @endif
                            </p>
                            <p class="flex items-center gap-1.5 text-[#6B665E]">
                                <x-z.icon name="location_on" class="text-[#C8956C] text-base" />{{ $cityLabel }}
                            </p>
                            <p class="flex items-center gap-1.5 text-[#6B665E]">
                                <x-z.icon name="calendar_month" class="text-[#C8956C] text-base" />
                                {{ $wr?->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr?->seasonLabel() ?? __('Flexible date')) }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $leads->links() }}</div>
        @endif
    </div>
@endsection
