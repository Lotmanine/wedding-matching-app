@extends('layouts.provider')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="font-serif text-3xl font-bold text-[#1A1A1A] mb-2">{{ __('provider.nav.feedback') }}</h1>
        <p class="text-[#6B665E] text-sm mb-8">{{ __('Un problème ou une suggestion ? Écrivez-nous.') }}</p>

        <form method="POST" action="{{ route('provider.feedback.store') }}" class="bg-white rounded-xl border border-[#D4CFC7]/50 shadow-sm p-5 sm:p-6 space-y-5">
            @csrf

            <div>
                <label for="fb-subject" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ __('Sujet') }}</label>
                <input type="text" id="fb-subject" name="subject" value="{{ old('subject') }}" required maxlength="150"
                       placeholder="{{ __("Ex. Impossible d'accepter une demande") }}"
                       class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors" />
                @error('subject') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="fb-message" class="block font-semibold text-sm text-[#1A1A1A] mb-1.5">{{ __('Message') }}</label>
                <textarea id="fb-message" name="message" rows="7" required maxlength="5000"
                          placeholder="{{ __("Décrivez ce qui s'est passé, sur quelle page, et ce que vous attendiez.") }}"
                          class="w-full px-4 py-2.5 bg-white border border-[#D4CFC7] rounded-lg text-sm text-[#1A1A1A] outline-none focus:border-[#C8956C] focus:ring-2 focus:ring-[#C8956C]/15 transition-colors resize-y">{{ old('message') }}</textarea>
                @error('message') <p class="mt-1 text-xs text-[#A84936]">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <x-z.btn type="submit" variant="accent" icon="send">{{ __('Envoyer') }}</x-z.btn>
            </div>
        </form>
    </div>
@endsection
