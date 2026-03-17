<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveOrganizationForUser
{
    /**
     * When no organization is set (e.g. no subdomain), resolve from user or use first org.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->has(Organization::class)) {
            return $next($request);
        }

        $user = $request->user();
        $organization = null;

        if ($user) {
            if ($user->hasRole('staff') && $user->organizations()->exists()) {
                $organization = $user->organizations()->first();
            } elseif ($user->hasRole('physician') && $user->physician?->organizations()->exists()) {
                $organization = $user->physician->organizations()->first();
            }
        }

        $organization ??= Organization::first();

        if ($organization) {
            app()->instance(Organization::class, $organization);
            view()->share('currentOrganization', $organization);
        }

        return $next($request);
    }
}
