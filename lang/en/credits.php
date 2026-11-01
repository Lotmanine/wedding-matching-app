<?php

declare(strict_types=1);

return [

    'title'           => 'Credits',
    'balance_label'   => 'Credit balance',
    'unlock_cost_line' => 'Accepting a request costs :count credit.|Accepting a request costs :count credits.',
    // Number and unit in one phrase ("6 credits").
    'count'           => ':count credit|:count credits',
    // Caption under a number shown on its own.
    'unit'            => 'credit|credits',
    'added_label'     => 'Credits added',
    'packs_title'     => 'Buy credits',
    'pack_per_unlock' => ':price per credit',
    'pack_cta'        => 'Choose this pack',

    'recent_title'    => 'Recent transactions',
    'full_history'    => 'See full history',
    'no_transactions' => 'No transactions yet.',

    'history_title'   => 'Credit history',
    'history_export'  => 'Export as CSV',
    'history_filter'  => 'Type',
    'history_all'     => 'All',

    'type' => [
        'purchase'         => 'Purchase',
        'unlock'           => 'Acceptance',
        'refund'           => 'Credits returned',
        'bonus'            => 'Bonus',
        'admin_adjustment' => 'Balance adjustment',
        'adjustment'       => 'Balance adjustment',
        'other'            => 'Credit movement',
    ],

    // Descriptions of entries written by the platform (see CreditLedger::systemDescription()).
    'entry' => [
        'purchase_pack'  => ':pack pack purchase · :uid',
        'purchase'       => 'Credit purchase',
        'unlock_request' => 'Acceptance of request :uid',
        'unlock'         => 'Request acceptance',
        'refund_request' => 'Credits returned after a report · request :uid',
        'refund'         => 'Credits returned after a report',
        'welcome_bonus'  => 'Welcome credits',
        'bonus'          => 'Free credits',
        'adjustment'     => 'Balance adjustment',
    ],

    'success_title'  => 'Payment confirmed',
    'success_sub'    => 'Your credits have been added.',
    'success_cta'    => 'See requests',
    'success_pdf'    => 'Download receipt',
    'success_pending_title' => 'Payment being confirmed',
    'success_pending_sub'   => 'Credits will be added once the payment is confirmed. This page refreshes automatically.',

    'pay' => [
        'title_choice'  => 'Payment method',
        'title_single'  => 'Payment method',
        'intro_both'    => 'Pay by card or by bank transfer.',
        'online_body'   => 'Test payment, no real charge. Credits are added once the payment is confirmed.',
        'offline_body'  => 'This is a demo, so no real transfer is needed: enter any reference. Credits are added once our team has checked it.',
    ],

    'failure_title'   => 'Payment not completed',
    'failure_sub'     => 'Your payment was not completed.',
    'failure_retry'   => 'Try again',
    'failure_contact' => 'Contact us',
    'failure_reasons' => [
        'gateway_failed'       => 'The payment service told us the payment failed.',
        'user_cancelled'       => 'The payment was canceled before it was confirmed.',
        'checkout_init_failed' => 'The payment page could not be opened.',
        'offline_rejected'     => 'Our team did not approve your transfer.',
        'unknown'              => 'The payment did not go through.',
    ],
    'failure_team_note'    => 'Reason given by our team:',
    'failure_causes_title' => 'Possible reasons a card payment fails:',
    'failure_causes'       => [
        'funds'  => 'not enough funds on the card;',
        'limit'  => 'payment limit reached;',
        'refuse' => 'transaction declined by the bank.',
    ],
    'failure_next'         => 'You can try again or use another card.',
    'failure_next_offline' => 'You can try again, use another card or pay by bank transfer.',

    'checkout' => [
        'failed'       => 'We could not start the payment. Please try again.',
        'no_url'       => 'Unexpected response from the payment service. Please try again.',
        'not_verified' => 'You can buy credits once your profile is approved.',
        'no_method'    => 'No payment method is available right now. Please contact our team.',
    ],

    'offline' => [
        'submitted'      => 'Transfer reported. Credits will be added once our team has checked it.',
        'pending_note'   => 'Transfer being checked. Credits will be added once it is approved.',
    ],

    'status' => [
        'paid'                 => 'Paid',
        'pending'              => 'Pending',
        'pending_verification' => 'Being checked',
        'failed'               => 'Failed',
        'cancelled'            => 'Canceled',
    ],

    'receipt' => [
        'title'        => 'Payment receipt',
        'download_pdf' => 'Download receipt',
        'issued_to'    => 'Issued to',
        'paid_on'      => 'Paid on',
        'reference'    => 'Reference',
        'gateway_ref'  => 'Payment reference',
        'transfer_ref' => 'Transfer reference',
        'status'       => 'Status',
        'amount'       => 'Amount',
        'demo_notice'  => 'Demo receipt, no real payment.',
        'generated_on' => 'Document generated on :date · Wedding Matching App.',
    ],

    'low_balance_warning' => 'Not enough credits to accept a request. Buy credits to continue.',
    'insufficient_redirect' => 'Not enough credits. Buy a pack to accept this request.',
];
