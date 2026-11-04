@extends('layouts.couple')

@section('description', __('Privacy policy of the Wedding Matching App portfolio demo and how it handles the data entered in it.'))

@section('content')
<x-legal-page :title="__('Privacy policy')" :subtitle="__('form.brand')" :updated="__('November 1, 2026')">

    <x-legal-section :title="__('1. A portfolio demonstration')">
        <p>{{ __('Wedding Matching App is a portfolio demonstration of a wedding-services marketplace. It is not a company, and no real service is offered here. Please do not enter real personal details.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('2. What the demo stores')">
        <p>{!! __('<strong>Requests:</strong> first name, WhatsApp number, city and optional district, wedding date, budget, services and, when given, the number of guests and details.') !!}</p>
        <p>{!! __('<strong>Provider accounts:</strong> name, business details, email address, phone and WhatsApp numbers, services, cities, prices, profile text and images, credit purchases and the requests accepted or declined.') !!}</p>
        <p>{!! __('<strong>Technical data:</strong> a shortened IP address and the browser type sent with each request, to limit abuse. Actions in the admin area are logged.') !!}</p>
    </x-legal-section>

    <x-legal-section :title="__('3. How long data is kept')">
        <p>{{ __('A request expires :days days after it is sent. The provider who accepts a request can see the contact details for :days days after accepting.', ['days' => (int) config('wedding.privacy.retention_days', 14)]) }}</p>
        <p>{{ __('Once the request has expired and that period is over, a scheduled job erases the identifying data: first name, WhatsApp number, district, details, IP address and browser type. It cannot erase anything a provider copied outside the demo. Only anonymous data remains: city, wedding date, services, number of guests and budget range.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('4. Who sees what')">
        <p>{{ __('Providers offered a request see its service, city, district, date, budget, number of guests and description, plus the first letter of the first name. For each service, only the provider who accepts sees the first name and WhatsApp number.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('5. Third parties')">
        <p>{{ __('A few technical services are involved in running the demo:') }}</p>
        <ul class="list-disc ps-5 space-y-1 mt-2">
            <li>{{ __('Hosting: the server that runs the demo and stores its data.') }}</li>
            <li>{{ __("AI ranking: a service that helps order providers for a request. It receives the service, city, district, date, budget and number of guests, plus the providers' professional profiles. It never receives the couple's name or phone number.") }}</li>
            <li>{{ __('WhatsApp (optional): when notifications are switched on, WhatsApp receives the phone number and the message.') }}</li>
        </ul>
    </x-legal-section>

    <x-legal-section :title="__('6. Cookies')">
        <p>{{ __('A language cookie remembers your language for a year. Session cookies keep you signed in and protect forms, and a provider who asks to stay signed in gets a longer-lasting one. There are no analytics or advertising cookies.') }}</p>
    </x-legal-section>

    <x-legal-section :title="__('7. Demo resets')">
        <p>{{ __('Demo data is reset regularly, so anything entered here can disappear at any time.') }}</p>
    </x-legal-section>

</x-legal-page>
@endsection
