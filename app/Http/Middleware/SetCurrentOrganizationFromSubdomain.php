<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganizationFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        $baseDomain = config('tenancy.base_domain') ?: parse_url(config('app.url'), PHP_URL_HOST);
        if (! $baseDomain || ! str_ends_with($host, $baseDomain)) {
            return $next($request);
        }

        $subdomain = rtrim(substr($host, 0, -strlen($baseDomain)), '.');

        if ($subdomain && ! in_array($subdomain, config('tenancy.ignored_subdomains', ['www']), true)) {
            $organization = Organization::where('subdomain', $subdomain)->first();

            if ($organization) {
                app()->instance(Organization::class, $organization);
                view()->share('currentOrganization', $organization);
            }
        }

        return $next($request);
    }
}

