@extends('layouts.provider')

@section('content')
    <div class="max-w-4xl">
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-gray-900 mb-6">
            {{ __('provider.profile.title') }}
        </h1>

        @livewire('provider.profile-editor')
    </div>
@endsection
