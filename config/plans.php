<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plan definitions
    |--------------------------------------------------------------------------
    |
    | Central configuration for all subscription plans. Used by the public
    | pricing page, organization plans page, and billing/limit logic.
    |
    */

    'plans' => [
        'essential' => [
            'name' => 'Essential',
            'tagline' => 'For small practices',
            'price' => 49,
            'limit' => 500,
            'featured' => false,
            'stripe_price_id' => env('STRIPE_PRICE_ESSENTIAL'),
            'features' => [
                'Up to :limit users & patients',
                'Secure messaging',
                'Patient history & notes',
                'Appointments & scheduling',
                'Email & SMS reminders',
            ],
        ],
        'professional' => [
            'name' => 'Professional',
            'tagline' => 'For growing practices',
            'price' => 149,
            'limit' => 2000,
            'featured' => true,
            'stripe_price_id' => env('STRIPE_PRICE_PROFESSIONAL'),
            'features' => [
                'Up to :limit users & patients',
                'Everything in Essential',
                'Priority support',
            ],
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'tagline' => 'For large practices & groups',
            'price' => 349,
            'limit' => 10000,
            'featured' => false,
            'stripe_price_id' => env('STRIPE_PRICE_ENTERPRISE'),
            'features' => [
                'Up to :limit users & patients',
                'Everything in Professional',
                'Dedicated support',
                'Custom options available',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan order (for display)
    |--------------------------------------------------------------------------
    */

    'order' => ['essential', 'professional', 'enterprise'],

    /*
    |--------------------------------------------------------------------------
    | Highest plan limit (for upgrade prompts and contact footer)
    |--------------------------------------------------------------------------
    */

    'highest_limit' => 10000,

    /*
    |--------------------------------------------------------------------------
    | Default plan limit (trial or no subscription)
    |--------------------------------------------------------------------------
    */

    'default_limit' => (int) env('BILLING_DEFAULT_PLAN_LIMIT', 500),

];
