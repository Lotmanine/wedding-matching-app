@extends('layouts.couple')

@section('description', __('Terms of use of the Wedding Matching App portfolio demo, a fictional marketplace with no real providers, payments or bookings.'))

@section('content')
<x-legal-page :title="__('Terms of use')" :subtitle="__('form.brand')" :updated="__('November 1, 2026')">

    <x-legal-section :title="__('1. About this demo')">
        <p>{{ __('Wedding Matching App is a portfolio demonstration of a wedding-services marketplace. It is not a company and does not offer a commercial service. The providers, requests and prices you see are examples.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('2. How the demo works')">
        <p>{{ __('A couple sends one wedding request covering one or more services. For each service, the request goes in rounds to verified providers matching the service and city. The first who accepts gets the contact details and contacts the couple on WhatsApp.') }}</p>
        <p>{{ __('Nothing guarantees that a provider will accept a request. Sending a request is free for couples, who would agree prices directly with the provider.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('3. Credits')">
        <p>{{ __('Providers buy credit packs. Viewing a request is free. Accepting one spends the displayed cost and reveals the first name and WhatsApp number. Accepting guarantees neither a reply nor a booking.') }}</p>
        <p>{{ __('Credits and payments exist for demonstration only and have no monetary value. Please do not make a real payment or bank transfer.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('4. Using the demo')">
        <p>{{ __('Please use made-up details only. Do not enter personal data about yourself or anyone else, and do not use the demo to contact, harass or mislead people.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('5. No guarantee')">
        <p>{{ __('The demo is provided as is. It can change, go offline or be reset at any time, and anything entered in it can be lost.') }}</p>
    </x-legal-section>

</x-legal-page>
@endsection
