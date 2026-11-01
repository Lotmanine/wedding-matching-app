@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.nav.webhooks') }}</h1>
            </div>
            <x-z.btn :href="route('admin.webhooks.create')" variant="accent" size="sm" icon="add">{{ __('Create a webhook') }}</x-z.btn>
        </div>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('Name') }}</th>
                            <th class="text-left px-5 py-4">{{ __('URL') }}</th>
                            <th class="text-left px-5 py-4">{{ __('Events') }}</th>
                            <th class="text-center px-5 py-4">{{ __('Deliveries') }}</th>
                            <th class="text-center px-5 py-4">{{ __('Active') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($endpoints as $e)
                            <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-[#1A1A1A]">{{ $e->name }}</td>
                                <td class="px-5 py-4 text-xs text-[#9A958D] font-mono truncate max-w-xs">{{ $e->url }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ((array) $e->events as $ev)
                                            <span class="text-[10px] bg-[#F0D9B5]/40 text-[#7A5530] px-2 py-0.5 rounded-full font-mono border border-[#C8956C]/30">{{ $ev }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center text-xs">
                                    <a href="{{ route('admin.webhooks.deliveries', $e->id) }}" class="inline-flex items-center gap-0.5 text-[#C8956C] font-bold hover:underline">{{ $e->total_deliveries }} <x-z.icon name="arrow_forward" class="text-sm" /></a>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <form method="POST" action="{{ route('admin.webhooks.toggle', $e->id) }}" class="inline">@csrf
                                        <button class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase border {{ $e->active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-[#F7F4F0] text-[#9A958D] border-[#D4CFC7]' }}">{{ $e->active ? __('Active') : __('Inactive') }}</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.webhooks.edit', $e->id) }}" class="text-[#C8956C] text-xs font-bold hover:underline">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.webhooks.destroy', $e->id) }}" class="inline ml-2" onsubmit="return confirm({{ \Illuminate\Support\Js::from(__('Delete this webhook?')) }});">
                                        @csrf @method('DELETE')
                                        <button class="text-[#A84936] text-xs font-bold hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No webhooks configured.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
