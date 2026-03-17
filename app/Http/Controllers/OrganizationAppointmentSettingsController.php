<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationAppointmentSettingsController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        $validated = $request->validate([
            'appointment_slot_minutes' => ['nullable', 'integer', Rule::in(Organization::SLOT_OPTIONS)],
            'business_hours_start' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'business_hours_end' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'business_days' => ['nullable', 'array'],
            'business_days.*' => ['integer', 'min:1', 'max:7'],
        ]);

        $data = array_filter([
            'appointment_slot_minutes' => $validated['appointment_slot_minutes'] ?? null,
            'business_hours_start' => $validated['business_hours_start'] ?? null,
            'business_hours_end' => $validated['business_hours_end'] ?? null,
            'business_days' => ! empty($validated['business_days'])
                ? array_values(array_map('intval', $validated['business_days']))
                : null,
        ], fn ($v) => $v !== null);

        if (! empty($data)) {
            $organization->update($data);
        }

        return redirect()
            ->route('organization.settings')
            ->with('status', __('Appointment settings updated.'));
    }
}
