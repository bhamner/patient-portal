<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default subscription price (Stripe Price ID)
    |--------------------------------------------------------------------------
    */

    'price_id' => env('STRIPE_PRICE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Trial length in days for new organization signups
    |--------------------------------------------------------------------------
    */

    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Invites require active subscription
    |--------------------------------------------------------------------------
    |
    | When true, only organizations with an active subscription (or on trial)
    | for the "default" plan can create and send invites.
    |
    */

    'invites_require_subscription' => env('BILLING_INVITES_REQUIRE_SUBSCRIPTION', true),

    /*
    |--------------------------------------------------------------------------
    | Plan user limits (Stripe Price ID => max users)
    |--------------------------------------------------------------------------
    |
    | Derived from config/plans.php. Maps Stripe Price IDs to the maximum
    | number of users (staff + physicians + patients) allowed on that plan.
    |
    */

    'plan_limits' => collect(config('plans.plans', []))
        ->filter(fn ($plan) => ! empty($plan['stripe_price_id']))
        ->mapWithKeys(fn ($plan, $key) => [$plan['stripe_price_id'] => $plan['limit']])
        ->filter()
        ->all(),

    /*
    |--------------------------------------------------------------------------
    | Default plan limit (trial or no subscription)
    |--------------------------------------------------------------------------
    */

    'default_plan_limit' => config('plans.default_limit', 500),

    /*
    |--------------------------------------------------------------------------
    | Highest plan limit (Enterprise)
    |--------------------------------------------------------------------------
    */

    'highest_plan_limit' => config('plans.highest_limit', 10000),

];
