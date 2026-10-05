@extends('layouts.provider')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-2">
            <h1 class="font-serif text-3xl font-bold text-[#1A1A1A]">
                {{ __('credits.history_title') }}
            </h1>
            <x-z.btn :href="route('provider.credits.history', ['format' => 'csv', 'type' => $type])" variant="outline" size="sm" icon="download">
                {{ __('credits.history_export') }}
            </x-z.btn>
        </div>
        <p class="text-[#6B665E] text-sm mb-8">{{ __('Toutes vos transactions de crédits.') }}</p>

        <form method="GET" action="{{ route('provider.credits.history') }}" class="flex flex-wrap gap-3 mb-6 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#9A958D] mb-1.5">{{ __('credits.history_filter') }}</label>
                <select name="type" class="px-3 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-all">
                    <option value="all" @selected($type === 'all')>{{ __('credits.history_all') }}</option>
                    @foreach (['purchase','unlock','refund','bonus','admin_adjustment'] as $t)
                        <option value="{{ $t }}" @selected($type === $t)>{{ __('credits.type.' . $t) }}</option>
                    @endforeach
                </select>
            </div>
            <x-z.btn type="submit" variant="primary" size="md" icon="filter_list">{{ __('Filtrer') }}</x-z.btn>
            @if ($type !== 'all')
                <a href="{{ route('provider.credits.history') }}" class="w-10 h-[42px] rounded-lg border border-[#D4CFC7] text-[#9A958D] hover:text-[#A84936] hover:border-[#A84936]/40 flex items-center justify-center shrink-0 transition-colors" aria-label="{{ __('credits.history_all') }}">
                    <x-z.icon name="close" class="text-lg" />
                </a>
            @endif
        </form>

        <div class="bg-white rounded-2xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            @if ($entries->isEmpty())
                <p class="text-sm text-[#9A958D] text-center py-12">{{ __('credits.no_transactions') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead>
                            <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                                <th class="text-start px-5 py-4">{{ __('Date') }}</th>
                                <th class="text-start px-5 py-4">{{ __('Type') }}</th>
                                <th class="text-start px-5 py-4">{{ __('Description') }}</th>
                                <th class="text-end px-5 py-4">{{ __('Mouvement') }}</th>
                                <th class="text-end px-5 py-4">{{ __('Solde') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D4CFC7]/20">
                            @foreach ($entries as $e)
                                <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                    <td class="px-5 py-4 text-xs text-[#9A958D] whitespace-nowrap">{{ $e->created_at?->isoFormat('DD/MM/YYYY HH:mm') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="text-[11px] px-2.5 py-0.5 rounded-full bg-[#F7F4F0] text-[#6B665E] border border-[#D4CFC7] font-bold uppercase tracking-wider">{{ $e->typeLabel() }}</span>
                                    </td>
                                    {{-- Platform-written entries are described in the reader's language; a note typed by a person is shown as stored. --}}
                                    @php $entryDescription = $e->systemDescription(); @endphp
                                    <td class="px-5 py-4 text-[#6B665E] max-w-xs truncate">
                                        @if ($entryDescription !== null)
                                            {{ $entryDescription }}
                                        @elseif (filled($e->notes))
                                            <span dir="auto">{{ $e->notes }}</span>
                                        @else
                                            {{ __('Non renseigné') }}
                                        @endif
                                    </td>
                                    <td @class([
                                        'px-5 py-4 text-end font-bold font-serif whitespace-nowrap',
                                        'text-green-600' => $e->delta > 0,
                                        'text-[#A84936]' => $e->delta < 0,
                                        'text-[#6B665E]' => $e->delta === 0,
                                    ])>
                                        {{ $e->delta > 0 ? '+' : '' }}{{ $e->delta }}
                                    </td>
                                    <td class="px-5 py-4 text-end text-[#1A1A1A] font-medium whitespace-nowrap">{{ $e->balance_after }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($entries->hasPages())
                    <div class="px-5 py-3 border-t border-[#D4CFC7]/30">{{ $entries->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection
