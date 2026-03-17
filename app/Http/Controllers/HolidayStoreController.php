<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HolidayStoreController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'name' => ['nullable', 'string', 'max:255'],
            'recurring' => ['boolean'],
        ]);

        $organization->holidays()->updateOrCreate(
            ['date' => $validated['date']],
            [
                'name' => $validated['name'] ?? null,
                'recurring' => (bool) ($validated['recurring'] ?? false),
            ]
        );

        return redirect()
            ->route('organization.settings')
            ->with('status', __('Holiday added.'));
    }
}
