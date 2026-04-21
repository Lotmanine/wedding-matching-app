@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <h1 class="font-display text-2xl font-bold mb-4">{{ __('admin.users.title') }}</h1>

        <form method="GET" class="flex gap-3 mb-4 items-end bg-white p-3 rounded-xl border border-gray-200">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher…" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 text-sm" />
            <select name="role" class="px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm">
                <option value="all" @selected($role === 'all')>Tous</option>
                <option value="admin" @selected($role === 'admin')>Admin</option>
                <option value="provider" @selected($role === 'provider')>Prestataire</option>
                <option value="couple" @selected($role === 'couple')>Couple</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-brand hover:bg-brand-dark text-white text-sm font-semibold transition">Filtrer</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="text-left px-4 py-3">{{ __('admin.users.col_name') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.users.col_email') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.users.col_role') }}</th>
                        <th class="text-left px-4 py-3">{{ __('admin.users.col_status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('admin.users.show', $u->id) }}'">
                            <td class="px-4 py-3">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-xs">{{ $u->roles->pluck('name')->join(', ') }}</td>
                            <td class="px-4 py-3 text-xs">{{ $u->status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-gray-500 text-sm">Aucun utilisateur.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($users->hasPages()) <div class="p-3 border-t border-gray-100">{{ $users->links() }}</div> @endif
        </div>
    </div>
@endsection
