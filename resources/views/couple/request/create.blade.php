@extends('layouts.couple')

@section('description', __('Describe your wedding once and the demo finds local providers for each service you need, from photo to catering. Fictional data, no real bookings.'))

@section('content')
    <div class="max-w-6xl mx-auto">
        @livewire('couple-request-form')
    </div>
@endsection
