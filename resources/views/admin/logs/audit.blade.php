@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <div class="mb-8">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.logs.title') }}</h1>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <input type="text" name="action" value="{{ $action }}" placeholder="{{ __('admin.logs.filter_action') }}" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors placeholder-[#9A958D]" />
            <input type="number" name="user_id" value="{{ $userId }}" placeholder="{{ __('User ID') }}" class="w-28 px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors placeholder-[#9A958D]" />
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">{{ __('Filter') }}</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('admin.logs.col_when') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.logs.col_actor') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.logs.col_action') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.logs.col_subject') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.logs.col_metadata') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-[#F7F4F0]/50 transition-colors">
                                <td class="px-5 py-3 text-xs text-[#9A958D] whitespace-nowrap">{{ $log->created_at?->isoFormat('DD/MM HH:mm:ss') }}</td>
                                <td class="px-5 py-3 text-xs text-[#6B665E]">{{ $log->user?->email ?? __('system') }}</td>
                                <td class="px-5 py-3 text-xs"><code class="font-mono font-bold text-[#C8956C] bg-[#F0D9B5]/40 px-2 py-0.5 rounded">{{ $log->action }}</code></td>
                                <td class="px-5 py-3 text-xs text-[#9A958D] font-mono">{{ class_basename((string) $log->subject_type) }}#{{ $log->subject_id ?? __('Not specified') }}</td>
                                <td class="px-5 py-3 text-xs"><pre class="text-[10px] text-[#6B665E] overflow-hidden max-w-md truncate">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '' }}</pre></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No entries.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages()) <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $logs->links() }}</div> @endif
        </div>
    </div>
@endsection
