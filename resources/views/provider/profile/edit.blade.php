@extends('layouts.provider')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="font-serif text-3xl font-bold text-[#1A1A1A] mb-8">
            {{ __('provider.profile.title') }}
        </h1>

        @livewire('provider.profile-editor')
    </div>
@endsection
