<?php

declare(strict_types=1);

return [

    // Brand + layout
    'brand'             => 'Wedding Matching App',
    'brand_tagline'     => 'Your wedding, made easier.',

    // Public navigation
    'nav_provider_area'   => 'Provider area',
    'nav_home'            => 'Home',
    'nav_submit_request'  => 'Send a request',
    'nav_for_providers'   => 'For providers',
    'nav_cta'             => 'Start my request',
    'cta_free_note'       => 'Free for couples',

    // Progress
    'step_of'           => 'Step :current of :total',
    'sidebar_journey'   => 'Your request',
    'services_count'    => '{1} :count service selected|[2,*] :count services selected',

    // Counts on the public pages
    'days_count'                   => '{1} :count day|[2,*] :count days',
    'cities_available'             => '{1} city available|[2,*] cities available',
    'credits_per_accepted_request' => '{1} credit per accepted request|[2,*] credits per accepted request',
    'credits_per_request'          => '{1} :count credit = 1 request|[2,*] :count credits = 1 request',

    // Step titles
    'step_1_title'      => 'How can we reach you?',
    'step_2_title'      => 'Where is the wedding?',
    'step_3_title'      => 'Budget and services',
    'step_4_title'      => 'Wedding date',
    'step_5_title'      => 'Check your request',

    // Step intros
    'step_1_intro'      => 'So a provider can contact you on WhatsApp.',
    'step_2_intro'      => 'Choose the city of the wedding.',
    'step_3_intro'      => 'Enter the total budget and the services you need.',
    'step_4_intro'      => 'Enter the planned date.',
    'step_5_intro'      => 'Check the details before sending.',

    // Field labels
    'label_first_name'     => 'First name',
    'label_whatsapp'       => 'WhatsApp number',
    'label_city'         => 'City',
    'label_district'        => 'District',
    'label_wedding_date'   => 'Wedding date',
    'label_guest_count'    => 'Number of guests',
    'label_budget'         => 'Total wedding budget',
    'label_services'       => 'Services you need',
    'label_description'    => 'Details (optional)',
    // Consent: the checkbox statement, then what is shared and with whom (help_accept_terms).
    'label_accept_terms'   => 'I agree to share my request with verified providers who cover my city.',
    'help_accept_terms'    => 'Wedding Matching App sends them the city, district, date, number of guests, budget, services and details. For each service, my first name and WhatsApp number go only to the provider who accepts my request. Retention periods are in the :link.',
    'accept_terms_link'    => 'privacy policy',
    'demo_notice'          => 'This is a demo: please do not enter real personal details.',

    // Placeholders / helpers
    'placeholder_first_name'  => 'E.g. Emma',
    'placeholder_whatsapp'    => '+12125550123',
    'placeholder_city'      => 'Choose a city…',
    'placeholder_district'     => 'Choose a district…',
    'placeholder_guest_count' => 'Choose the number of guests',
    'placeholder_description' => 'E.g. photo style, menu or decoration you have in mind.',

    'help_whatsapp'      => 'International format with no spaces. Example: +12125550123 or +33612345678.',
    'help_whatsapp_use'  => 'The provider who accepts your request contacts you on this number.',
    'help_district'       => 'Choose the city first.',
    'help_services'      => 'Choose at least one service.',
    'help_guest_count'   => 'Needed for catering or a wedding venue.',
    'help_char_count'    => ':count / :max characters',

    // Buttons
    'btn_previous'      => 'Back',
    'btn_next'          => 'Continue',
    'btn_submit'        => 'Send my request',
    'btn_submitting'    => 'Sending…',
    'btn_home'          => 'Back to home',
    'btn_copy'          => 'Copy reference',
    'btn_copied'        => 'Reference copied',

    // Review
    'review_title'       => 'Summary',
    'review_couple'      => 'Your contact details',
    'review_location'    => 'Wedding location',
    'review_date'        => 'Wedding date',
    'review_date_guests' => 'Date and guests',
    'review_budget'      => 'Budget',
    'review_services'    => 'Services you need',
    'review_notes'       => 'Details',
    'review_none'        => 'Not provided',

    // Confirmation
    'confirm_title'       => 'Your request has been sent',
    'confirm_subtitle'    => 'We are passing it on to verified providers in your city.',
    'confirm_reference'   => 'Request reference',
    'confirm_keep_link'   => 'Keep this link to follow your request.',
    'confirm_next_steps'  => 'What happens next?',
    'confirm_step_1'      => 'For each service, we notify up to :per_round verified providers in your city. Each has :hours hours to respond.',
    'confirm_step_2'      => 'The first to accept contacts you on WhatsApp, and you agree on the price together. If nobody accepts, we notify other providers, then the request becomes visible to all eligible providers in your city.',
    'confirm_step_3'      => 'Follow the progress on this page. If nobody accepts before the expiry date, the request expires.',
    'confirm_services'    => 'Services you need',
    'confirm_status_searching' => 'Search in progress',
    'confirm_status_found'     => 'Request accepted',
    'confirm_status_open'      => 'Wider search',
    'confirm_status_open_help' => 'The request is now visible to all eligible providers in your city.',
    'confirm_status_none'      => 'Expired with no acceptance',
    'confirm_expiry'      => 'Your request expires on :date. If a provider accepts it, they keep access to your contact details for :days days after accepting. Your personal details are erased from the platform once the request has expired and that period is over.',
    'confirm_new_request' => 'Send a new request',

    // Submission errors
    'rate_limit'          => 'Too many requests sent. Try again in :minutes min.',
    'submission_failed'   => 'Your request was not saved. Please try again.',
];
