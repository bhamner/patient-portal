<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HolidayDestroyController extends Controller
{
    public function __invoke(Request $request, OrganizationHoliday $holiday): RedirectResponse
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization || $holiday->organization_id !== $organization->id) {
            abort(404);
        }

        $holiday->delete();

        return redirect()
            ->route('organization.settings')
            ->with('status', __('Holiday removed.'));
    }
}
