<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base domain for organization subdomains
    |--------------------------------------------------------------------------
    |
    | This is used to resolve organizations from subdomains, e.g.
    | {subdomain}.patient-portal.example.com.
    |
    | For HIPAA-aligned deployments, ensure this host is served strictly
    | over HTTPS and fronted by a properly configured WAF / load balancer.
    |
    */

    'base_domain' => env('TENANCY_BASE_DOMAIN', parse_url(config('app.url'), PHP_URL_HOST)),

    /*
    |--------------------------------------------------------------------------
    | Subdomains that should never be treated as organizations
    |--------------------------------------------------------------------------
    */

    'ignored_subdomains' => [
        'www',
        'app',
    ],
];

