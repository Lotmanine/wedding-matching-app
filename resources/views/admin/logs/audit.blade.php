@extends('layouts.admin')

@section('content')
    <div class="max-w-6xl">
        <h1 class="font-display text-2xl font-bold mb-4">{{ __('admin.logs.title') }}</h1>

        <form method="GET" class="flex gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <input type="text" name="action" value="{{ $action }}" placeholder="{{ __('admin.logs.filter_action') }}" class="px-3 py-2 rounded-lg border border-gray-200 text-sm" />
            <input type="number" name="user_id" value="{{ $userId }}" placeholder="User ID" class="px-3 py-2 rounded-lg border border-gray-200 text-sm w-28" />
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">{{ __('admin.logs.col_when') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.logs.col_actor') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.logs.col_action') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.logs.col_subject') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.logs.col_metadata') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->isoFormat('DD/MM HH:mm:ss') }}</td>
                            <td class="px-4 py-2 text-xs">{{ $log->user?->email ?? 'système' }}</td>
                            <td class="px-4 py-2 text-xs font-mono">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-xs text-gray-500 font-mono">{{ class_basename((string) $log->subject_type) }}#{{ $log->subject_id ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs"><pre class="text-[10px] overflow-hidden max-w-md truncate">{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '' }}</pre></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-gray-500 text-sm">Aucune entrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($logs->hasPages()) <div class="p-3 border-t border-gray-100">{{ $logs->links() }}</div> @endif
        </div>
    </div>
@endsection
