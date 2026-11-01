@extends('layouts.provider')

@section('content')
    @php
        $wr = $wedRequest;
        $cityLabel = \App\Models\City::where('code', (string) $wr->couple_city_code)->value('name') ?? $wr->couple_city_code;
        $whatsappDigits = preg_replace('/\D+/', '', (string) $wr->couple_whatsapp);
        $whatsappLink = config('wedding.whatsapp.link_prefix') . $whatsappDigits;
        $phoneLink = 'tel:' . preg_replace('/\s+/', '', (string) $wr->couple_whatsapp);
        $anonymized = $lead->isAnonymized();
        $anonymizeAt = $lead->anonymizeAt();
        $serviceIcon = match ($lead->service_type?->value) {
            'photo' => 'camera_alt', 'video' => 'videocam', 'catering' => 'restaurant', 'decoration' => 'local_florist',
            'pastry' => 'cake', 'venue' => 'villa', 'music' => 'music_note', 'beauty' => 'face_retouching_natural',
            default => 'category',
        };
    @endphp

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('provider.leads.index') }}" class="flex items-center gap-2 text-sm text-[#6B665E] hover:text-[#1A1A1A] transition-colors font-medium">
                <x-z.icon name="arrow_back" class="text-lg" /> {{ __('provider.unlocked.back_to_leads') }}
            </a>
        </div>

        {{-- Status banner: the heading follows the real state of the contact details --}}
        <div @class([
            'mb-3 border rounded-xl p-4 flex items-center gap-3',
            'bg-green-50 border-green-200' => ! $anonymized,
            'bg-[#F7F4F0] border-[#D4CFC7]/60' => $anonymized,
        ])>
            <x-z.icon name="check_circle" :filled="true" class="text-2xl shrink-0 {{ $anonymized ? 'text-[#9A958D]' : 'text-green-600' }}" />
            <div>
                <p @class(['text-sm font-bold', 'text-green-800' => ! $anonymized, 'text-[#1A1A1A]' => $anonymized])>{{ $anonymized ? __('provider.unlocked.header_expired') : __('provider.unlocked.header') }}</p>
                <p @class(['text-xs', 'text-green-600' => ! $anonymized, 'text-[#6B665E]' => $anonymized])>
                    {{ __('provider.unlocked.accepted_on', ['when' => $lead->unlocked_at?->diffForHumans() ?? __('Not provided')]) }}
                    · {{ trans_choice('provider.unlocked.credits_charged', (int) $lead->credits_charged, ['count' => (int) $lead->credits_charged]) }}
                </p>
            </div>
        </div>

        {{-- Access-window notice --}}
        @if ($anonymized)
            <div class="mb-6 bg-[#F7F4F0] border border-[#D4CFC7]/60 rounded-xl p-4 flex items-center gap-3">
                <x-z.icon name="visibility_off" class="text-[#9A958D] text-2xl shrink-0" />
                <div>
                    <p class="text-sm font-bold text-[#1A1A1A]">{{ __('Contact details hidden') }}</p>
                    <p class="text-xs text-[#6B665E]">{{ __("The access period (:days days after you accepted) has ended. The couple's contact details are no longer shown.", ['days' => (int) config('wedding.privacy.retention_days', 14)]) }}</p>
                </div>
            </div>
        @elseif ($anonymizeAt)
            <div class="mb-6 bg-[#F0D9B5]/30 border border-[#C8956C]/30 rounded-xl p-4 flex items-center gap-3">
                <x-z.icon name="schedule" class="text-[#C8956C] text-2xl shrink-0" />
                <div>
                    <p class="text-sm font-bold text-[#1A1A1A]">{{ __('Contact details shown until :date', ['date' => $anonymizeAt->isoFormat('LL')]) }}</p>
                    <p class="text-xs text-[#6B665E]">{{ __("After this date, the couple's contact details are no longer shown (personal data protection). Contact the couple before this date.") }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Main column --}}
            <section class="lg:col-span-2 bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6 md:p-8 space-y-6">
                <header class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-[#F0D9B5]/60 flex items-center justify-center shrink-0">
                        <x-z.icon :name="$serviceIcon" class="text-[#C8956C] text-2xl" />
                    </div>
                    <h1 class="font-serif text-2xl md:text-3xl font-bold text-[#1A1A1A]">{{ $lead->service_type?->label() }}</h1>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="location_on" class="text-[#C8956C] text-base shrink-0" />{{ $cityLabel }}{{ $wr->couple_district ? ' · ' . $wr->couple_district : '' }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="calendar_month" class="text-[#C8956C] text-base shrink-0" />{{ $wr->wedding_date ? $wr->wedding_date->isoFormat('LL') : ($wr->seasonLabel() ?? __('Not provided')) }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="group" class="text-[#C8956C] text-base shrink-0" />{{ $wr->guest_count?->label() ?? __('Not provided') }}
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm text-[#6B665E] font-medium">
                        <x-z.icon name="payments" class="text-[#C8956C] text-base shrink-0" />{{ $wr->budget_range?->label($wr->currency()) ?? __('Not provided') }}
                    </div>
                </div>

                @if ($wr->description)
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-2">{{ __('Description') }}</p>
                        <p class="text-[#1A1A1A] italic leading-relaxed">{{ __('provider.leads.quoted', ['text' => $wr->description]) }}</p>
                    </div>
                @endif

                {{-- Contact advice is pointless once the contact details are no longer shown --}}
                @unless ($anonymized)
                    <div class="p-4 rounded-xl bg-[#F0D9B5]/30 border border-[#C8956C]/20 text-sm">
                        <p class="font-bold text-[#1A1A1A] flex items-center gap-2"><x-z.icon name="lightbulb" :filled="true" class="text-[#C8956C] text-lg" />{{ __('provider.unlocked.tip_title') }}</p>
                        <p class="mt-1 text-[#6B665E] leading-relaxed">{{ __('provider.unlocked.tip_body') }}</p>
                    </div>
                @endunless
            </section>

            {{-- Contact card (revealed) --}}
            <aside class="space-y-4">
                <div class="bg-white rounded-2xl border border-[#C8956C]/30 shadow-sm p-6 space-y-5">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D]">{{ __('provider.unlocked.contact_title') }}</p>

                    <div class="space-y-3">
                        <div class="p-4 bg-[#F0D9B5]/30 rounded-xl border border-[#C8956C]/20">
                            <p class="text-xs text-[#9A958D] mb-1">{{ __('provider.unlocked.couple_name') }}</p>
                            <p class="font-bold text-[#1A1A1A] text-base">{{ $anonymized ? __('Not available') : $wr->couple_first_name }}</p>
                        </div>
                        <div class="flex items-start gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm">
                            <x-z.icon name="location_on" class="text-[#C8956C] text-base shrink-0" />
                            <div>
                                <span class="text-xs text-[#9A958D] block">{{ __('provider.unlocked.couple_city') }}</span>
                                <span class="font-medium text-[#1A1A1A]">{{ $cityLabel }}</span>
                            </div>
                        </div>
                        @if ($wr->couple_district)
                            <div class="flex items-start gap-1.5 px-3 py-2 bg-[#F7F4F0] rounded-lg text-sm">
                                <x-z.icon name="pin_drop" class="text-[#C8956C] text-base shrink-0" />
                                <div>
                                    <span class="text-xs text-[#9A958D] block">{{ __('provider.unlocked.couple_district') }}</span>
                                    <span class="font-medium text-[#1A1A1A]">{{ $wr->couple_district }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="p-4 bg-[#25D366]/10 rounded-xl border border-[#25D366]/30">
                            <p class="text-xs text-[#9A958D] mb-1">{{ __('provider.unlocked.couple_whatsapp') }}</p>
                            <p class="font-mono font-bold text-[#1A1A1A] select-all"><span>{{ $anonymized ? __('Not available') : $wr->couple_whatsapp }}</span></p>
                        </div>
                    </div>

                    @unless ($anonymized)
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-lg bg-[#25D366] text-white font-semibold text-sm hover:bg-[#1ebe5d] shadow-sm hover:shadow-md transition-all active:scale-[0.97]">
                            <x-z.icon name="chat" :filled="true" class="text-lg" /> {{ __('provider.unlocked.open_whatsapp') }}
                        </a>
                        <a href="{{ $phoneLink }}"
                           class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-lg bg-white border border-[#D4CFC7] text-[#1A1A1A] font-semibold text-sm hover:bg-[#F7F4F0] hover:border-[#C8956C] transition-all">
                            <x-z.icon name="call" class="text-lg" /> {{ __('provider.unlocked.call') }}
                        </a>
                    @endunless
                </div>

                {{-- Dispute: report a dead request to get the credits back --}}
                <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm p-6">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-3">{{ __('A problem with this request?') }}</p>

                    @if (($dispute ?? null) !== null)
                        <div @class([
                            'p-3 rounded-lg text-sm border',
                            'bg-[#F0D9B5]/30 border-[#C8956C]/30 text-[#7A5530]' => $dispute->status === 'pending',
                            'bg-green-50 border-green-200 text-green-800'        => $dispute->status === 'approved',
                            'bg-red-50 border-red-100 text-[#A84936]'            => $dispute->status === 'rejected',
                        ])>
                            @if ($dispute->status === 'pending')
                                <p class="font-semibold">{{ __('Report under review') }}</p>
                                <p class="text-xs mt-0.5">{{ __('Reason: :reason · sent :when', ['reason' => $dispute->reasonLabel(), 'when' => $dispute->created_at->diffForHumans()]) }}</p>
                            @elseif ($dispute->status === 'approved')
                                <p class="font-semibold">{{ __('Report approved: credits returned') }}</p>
                                <p class="text-xs mt-0.5">{{ $dispute->decided_at?->diffForHumans() }}</p>
                            @else
                                <p class="font-semibold">{{ __('Report declined') }}</p>
                                @if ($dispute->admin_note)<p class="text-xs mt-0.5">{{ $dispute->admin_note }}</p>@endif
                            @endif
                        </div>
                    @else
                        <details class="group">
                            <summary class="cursor-pointer text-sm font-semibold text-[#6B665E] hover:text-[#A84936] transition-colors list-none flex items-center gap-1.5">
                                <x-z.icon name="flag" class="text-base" /> {{ __('Report a problem') }}
                            </summary>
                            <form method="POST" action="{{ route('provider.leads.dispute', $lead->service_uid) }}" class="mt-3 space-y-2.5">
                                @csrf
                                <select name="reason" required class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15">
                                    <option value="">{{ __('Reason for the report…') }}</option>
                                    <option value="wrong_number">{{ __('Wrong or unreachable number') }}</option>
                                    <option value="no_response">{{ __('No reply from the couple') }}</option>
                                    <option value="fake">{{ __('Fake request or spam') }}</option>
                                    <option value="other">{{ __('Other') }}</option>
                                </select>
                                <textarea name="details" rows="2" maxlength="500" placeholder="{{ __('Details (optional)') }}"
                                          class="w-full px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 resize-none"></textarea>
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-[#A84936]/40 text-[#A84936] font-semibold text-sm hover:bg-red-50 transition-colors">
                                    {{ __('Send the report') }}
                                </button>
                                <p class="text-[11px] text-[#9A958D]">{{ __('If our team confirms the problem, the credits you spent are returned.') }}</p>
                            </form>
                        </details>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection
