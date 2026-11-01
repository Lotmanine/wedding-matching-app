@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl">
        <div class="mb-8 flex items-start justify-between gap-3">
            <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('admin.users.title') }}</h1>
            <x-z.btn :href="route('admin.users.create')" variant="accent" size="sm" icon="person_add">{{ __('Create a user') }}</x-z.btn>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end bg-white p-4 rounded-xl border border-[#D4CFC7]/50 shadow-sm">
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search…') }}" class="flex-1 min-w-[200px] px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors placeholder-[#9A958D]" />
            <select name="role" class="px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors">
                <option value="all" @selected($role === 'all')>{{ __('All') }}</option>
                <option value="admin" @selected($role === 'admin')>{{ __('admin.common.admin') }}</option>
                <option value="manager" @selected($role === 'manager')>{{ __('Manager') }}</option>
                <option value="provider" @selected($role === 'provider')>{{ __('admin.common.provider') }}</option>
                <option value="couple" @selected($role === 'couple')>{{ __('Couple') }}</option>
            </select>
            <x-z.btn type="submit" variant="primary" size="sm" icon="filter_list">{{ __('Filter') }}</x-z.btn>
        </form>

        <div class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-[#F7F4F0] text-[#9A958D] text-[10px] font-bold uppercase tracking-wider border-b border-[#D4CFC7]/40">
                            <th class="text-left px-5 py-4">{{ __('admin.users.col_name') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.users.col_email') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.users.col_role') }}</th>
                            <th class="text-left px-5 py-4">{{ __('admin.users.col_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D4CFC7]/20">
                        @forelse ($users as $u)
                            <tr class="hover:bg-[#F7F4F0]/50 cursor-pointer transition-colors" onclick="window.location='{{ route('admin.users.show', $u->id) }}'">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-[#F0D9B5] flex items-center justify-center text-xs font-bold text-[#1A1A1A] shrink-0 uppercase">{{ mb_substr($u->name, 0, 1) }}</div>
                                        <span class="font-semibold text-[#1A1A1A]">{{ $u->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $u->email }}</td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $u->roles->pluck('name')->join(', ') }}</td>
                                <td class="px-5 py-4 text-xs text-[#6B665E]">{{ $u->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="p-6 text-center text-[#9A958D] text-sm">{{ __('No users.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages()) <div class="px-5 py-4 border-t border-[#D4CFC7]/20">{{ $users->links() }}</div> @endif
        </div>
    </div>
@endsection
