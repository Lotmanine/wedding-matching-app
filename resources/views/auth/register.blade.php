<x-guest-layout>
    <div class="mb-6">
        <h1 class="font-serif text-2xl font-bold text-[#1A1A1A]">{{ __('Create an account') }}</h1>
        <p class="mt-1 text-sm text-[#6B665E]">{{ __('Join Wedding Matching App.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Full name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('name')" class="text-xs text-[#A84936]" />
        </div>

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Email address') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('email')" class="text-xs text-[#A84936]" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label for="password" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('password')" class="text-xs text-[#A84936]" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
            <label for="password_confirmation" class="block text-sm font-semibold text-[#1A1A1A]">{{ __('Confirm password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="w-full px-4 py-3.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] placeholder-[#9A958D] outline-none transition-all duration-200 focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="text-xs text-[#A84936]" />
        </div>

        <x-z.btn type="submit" variant="primary" size="lg" class="w-full">{{ __('Create my account') }}</x-z.btn>

        <div class="text-center">
            <a class="text-sm font-semibold text-[#C8956C] hover:text-[#B8845B] hover:underline" href="{{ route('login') }}">
                {{ __('Already registered? Log in') }}
            </a>
        </div>
    </form>
</x-guest-layout>
