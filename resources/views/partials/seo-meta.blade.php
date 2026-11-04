{{-- Search and link-preview tags of the public pages. A page sets its own
     description with @section('description'); the home description is the default.
     Section values are already escaped by Blade, so they are printed as is. --}}
@php
    $seoTitle = trim($__env->yieldContent('title', $title ?? __('form.brand')));
    $seoDescription = trim($__env->yieldContent('description', __('Portfolio demo of a wedding-services marketplace built with Laravel and Livewire. Couples send one request and local providers are matched through timed rounds.')));
    $seoUrl = url()->current();
    $seoImage = asset('images/og-image.jpg');
    $seoImageAlt = __('Home page of the Wedding Matching App demo');
@endphp
    <meta name="description" content="{!! $seoDescription !!}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('form.brand') }}">
    <meta property="og:title" content="{!! $seoTitle !!}">
    <meta property="og:description" content="{!! $seoDescription !!}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'fr' ? 'fr_FR' : 'en_US' }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $seoImageAlt }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{!! $seoTitle !!}">
    <meta name="twitter:description" content="{!! $seoDescription !!}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <meta name="twitter:image:alt" content="{{ $seoImageAlt }}">
