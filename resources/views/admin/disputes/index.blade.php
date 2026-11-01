@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <div class="mb-6">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Disputes: credits to refund') }}</h1>
            <p class="text-sm text-[#6B665E] mt-1">{{ __('Reports of dead requests (wrong number, no reply, fake). Approving refunds the credits spent on the request.') }}</p>
        </div>

        {{-- Status filter tabs --}}
        <div class="flex items-center gap-1 p-1 bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm w-fit mb-5">
            @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'all' => __('All')] as $st => $label)
                <a href="{{ route('admin.disputes.index', ['status' => $st]) }}"
                   @class([
                       'flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all',
                       'bg-[#1A1A1A] text-white' => $status === $st,
                       'text-[#6B665E] hover:text-[#1A1A1A]' => $status !== $st,
                   ])>
                    {{ $label }}
                    @if (isset($counts[$st]) && $counts[$st] > 0)
                        <span @class([
                            'text-[10px] font-bold px-1.5 py-0.5 rounded-full',
                            'bg-[#C8956C] text-white' => $status === $st,
                            'bg-[#F0D9B5] text-[#7A5530]' => $status !== $st,
                        ])>{{ $counts[$st] }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($disputes->isEmpty())
            <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-10 text-center text-sm text-[#9A958D]">
                {{ $status !== 'all' ? __('No disputes (:status).', ['status' => $status]) : __('No disputes.') }}
            </div>
        @else
            <div class="space-y-4">
                @foreach ($disputes as $d)
                    <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span @class([
                                        'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full',
                                        'bg-[#F0D9B5] text-[#7A5530]' => $d->status === 'pending',
                                        'bg-green-100 text-green-800' => $d->status === 'approved',
                                        'bg-red-50 text-[#A84936]'    => $d->status === 'rejected',
                                    ])>{{ $d->status }}</span>
                                    <span class="font-semibold text-sm text-[#1A1A1A]">{{ $d->reasonLabel() }}</span>
                                    <span class="text-xs text-[#9A958D]">· {{ $d->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-[#6B665E] mt-1.5">
                                    <a href="{{ route('admin.service_requests.show', $d->serviceRequest?->service_uid) }}" class="font-mono text-xs text-[#C8956C] hover:underline">{{ $d->serviceRequest?->service_uid }}</a>
                                    · {{ $d->serviceRequest?->service_type?->label() }}
                                    · {{ __('Provider:') }}
                                    <a href="{{ route('admin.providers.show', $d->provider_user_id) }}" class="text-[#C8956C] hover:underline">{{ $d->provider?->providerProfile?->business_name ?? $d->provider?->email }}</a>
                                    · {{ trans_choice(':count credit at stake|:count credits at stake', max(1, (int) ($d->serviceRequest?->credits_charged ?? 1))) }}
                                </p>
                                @if ($d->details)
                                    <p class="text-sm text-[#1A1A1A] italic mt-2">“{{ $d->details }}”</p>
                                @endif
                                @if ($d->status !== 'pending')
                                    <p class="text-xs text-[#9A958D] mt-2">
                                        {{ __('Decided by :name :when', ['name' => $d->decidedBy?->name ?? __('Not specified'), 'when' => $d->decided_at?->diffForHumans() ?? '']) }}
                                        @if ($d->admin_note) · {{ $d->admin_note }} @endif
                                    </p>
                                @endif
                            </div>

                            @if ($d->status === 'pending')
                                <div class="flex flex-col gap-2 shrink-0 w-full sm:w-56">
                                    <form method="POST" action="{{ route('admin.disputes.approve', $d->id) }}">
                                        @csrf
                                        <x-z.btn type="submit" variant="accent" size="sm" class="w-full justify-center"><x-z.icon name="check" class="text-base" /> {{ __('Refund') }}</x-z.btn>
                                    </form>
                                    <details>
                                        <summary class="cursor-pointer text-xs text-[#9A958D] hover:text-[#A84936] text-center transition-colors">{{ __('Reject…') }}</summary>
                                        <form method="POST" action="{{ route('admin.disputes.reject', $d->id) }}" class="mt-2 space-y-2">
                                            @csrf
                                            <input type="text" name="admin_note" required maxlength="500" placeholder="{{ __('Reason for rejection (visible to the provider)') }}"
                                                   class="w-full px-3 py-2 bg-white border border-[#D4CFC7] rounded-lg text-xs outline-none focus:border-[#A84936] focus:ring-2 focus:ring-[#A84936]/15" />
                                            <x-z.btn type="submit" variant="danger" size="sm" class="w-full justify-center">{{ __('Confirm rejection') }}</x-z.btn>
                                        </form>
                                    </details>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">{{ $disputes->links() }}</div>
        @endif
    </div>
@endsection
