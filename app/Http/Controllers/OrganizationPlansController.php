<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Contracts\View\View;

class OrganizationPlansController extends Controller
{
    public function __invoke(): View
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        return view('pages.organization.plans', [
            'organization' => $organization,
        ]);
    }
}
