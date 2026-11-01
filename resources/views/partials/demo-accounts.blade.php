{{-- Public demo logins. $accounts: keys of config('wedding.demo.accounts'). Fills the login form on this page. --}}
@if (config('wedding.demo.enabled'))
    <div class="mt-6 rounded-xl border border-[#C8956C]/40 bg-[#F0D9B5]/30 p-4">
        <p class="text-sm font-semibold text-[#1A1A1A]">{{ __('Demo accounts') }}</p>
        <p class="mt-0.5 text-xs text-[#6B665E]">{{ __('Test the app with these accounts. Data resets every day.') }}</p>
        <ul class="mt-3 space-y-2">
            @foreach ($accounts as $key)
                @php $account = config("wedding.demo.accounts.{$key}"); @endphp
                <li class="flex items-center justify-between gap-3 rounded-lg bg-white/70 px-3 py-2">
                    <div class="min-w-0 text-xs">
                        <p class="font-semibold text-[#1A1A1A]">{{ __($account['label']) }}</p>
                        <p class="truncate text-[#6B665E]">{{ $account['email'] }} · {{ $account['password'] }}</p>
                    </div>
                    <button type="button"
                        class="shrink-0 rounded-md border border-[#D4CFC7] bg-white px-2.5 py-1 text-xs font-semibold text-[#1A1A1A] hover:border-[#C8956C]"
                        onclick="document.getElementById('email').value = {{ \Illuminate\Support\Js::from($account['email']) }}; document.getElementById('password').value = {{ \Illuminate\Support\Js::from($account['password']) }};">{{ __('Use') }}</button>
                </li>
            @endforeach
        </ul>
    </div>
@endif
