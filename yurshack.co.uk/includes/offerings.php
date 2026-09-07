<?php
declare(strict_types=1);

/**
 * Published service menu from the Master Website Service Agreement
 * (standard pricing). Offered prices are negotiated on the order form.
 */
return [
    'domain_arrangements' => [
        'platform_subdomain' => [
            'label' => 'Platform subdomain',
            'summary' => 'A subdomain such as customer.yurshack.co.uk — no custom-domain setup fee.',
            'setup_fee' => false,
            'annual_domain_fee' => false,
        ],
        'customer_domain' => [
            'label' => 'Customer-provided custom domain',
            'summary' => 'You keep ownership; we configure DNS and hosting. Custom-domain setup fee applies.',
            'setup_fee' => true,
            'annual_domain_fee' => false,
        ],
        'provider_domain' => [
            'label' => 'Provider-arranged custom domain',
            'summary' => 'We register and configure a domain for you. Setup fee and annual domain fee apply.',
            'setup_fee' => true,
            'annual_domain_fee' => true,
        ],
    ],
    'services' => [
        'website_build' => [
            'label' => 'Website build',
            'summary' => 'Agreed pages and build scope for your business site.',
            'billing' => 'one_off',
            'standard_price' => 995,
            'standard_display' => '£995 one-off',
            'unit' => 'one-off',
            'selectable' => true,
        ],
        'managed_hosting' => [
            'label' => 'Managed hosting + basic changes',
            'summary' => 'Hosting, SSL, backups, and up to 30 minutes/month for text, images, opening hours, prices, and contact details. No rollover.',
            'billing' => 'monthly',
            'standard_price' => 79,
            'standard_display' => '£79/month',
            'unit' => 'month',
            'selectable' => true,
        ],
        'custom_domain_setup' => [
            'label' => 'Custom-domain setup',
            'summary' => 'DNS/hosting configuration for a customer-provided or Provider-arranged custom domain.',
            'billing' => 'one_off',
            'standard_price' => 195,
            'standard_display' => '£195 one-off',
            'unit' => 'one-off',
            'selectable' => true,
        ],
        'co_uk_domain' => [
            'label' => 'Provider-arranged standard .co.uk domain',
            'summary' => 'Registration and annual renewal for a standard .co.uk domain. Premium or non-standard domains quoted separately.',
            'billing' => 'yearly',
            'standard_price' => 25,
            'standard_display' => '£25/year',
            'unit' => 'year',
            'selectable' => true,
        ],
        'code_handover' => [
            'label' => 'Website/code handover',
            'summary' => 'Source-code/repository handover, deployment notes, and one handover session.',
            'billing' => 'one_off',
            'standard_price' => 695,
            'standard_display' => '£695 one-off',
            'unit' => 'one-off',
            'selectable' => true,
        ],
        'additional_dev' => [
            'label' => 'Additional changes or development',
            'summary' => 'New pages, redesign, new functionality, copywriting, integrations, or work outside basic changes.',
            'billing' => 'hourly',
            'standard_price' => 75,
            'standard_display' => '£75/hour',
            'unit' => 'hour',
            'selectable' => true,
        ],
    ],
    'terms_highlights' => [
        'Setup fees are payable before work starts; recurring fees monthly in advance unless agreed otherwise.',
        'Managed hosting includes email support for faults and 30 minutes/month of basic content changes.',
        'Monthly services can be cancelled on 30 days’ written notice, subject to accrued fees and committed third-party costs.',
        'Fees are exclusive of VAT unless stated otherwise.',
    ],
];
