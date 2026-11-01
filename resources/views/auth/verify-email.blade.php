<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Verify your email address') }}</h1>
        <p class="mt-2 text-sm text-[#6B665E] leading-relaxed">
            {{ __("Thanks for signing up! Before you start, please verify your email address by clicking the link we just sent you. If you didn't receive it, we'll gladly send you another.") }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 rounded-lg bg-[#F0D9B5]/40 border border-[#C8956C]/30 text-sm font-medium text-[#7A5530]">
            {{ __('A new verification link has been sent to the email address you gave when signing up.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-z.btn type="submit" variant="primary" size="md">{{ __('Resend the verification email') }}</x-z.btn>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
