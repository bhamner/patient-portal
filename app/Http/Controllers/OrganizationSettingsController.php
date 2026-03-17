<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationHoliday;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class OrganizationSettingsController extends Controller
{
    public function __invoke(): View
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        $slotMinutes = $organization->appointment_slot_minutes
            ? (int) $organization->appointment_slot_minutes
            : (int) config('appointments.slot_minutes', 30);
        $slotMinutes = in_array($slotMinutes, Organization::SLOT_OPTIONS, true) ? $slotMinutes : 30;

        $businessHoursStart = $organization->business_hours_start
            ?: config('appointments.business_hours_start', '08:00');
        $businessHoursEnd = $organization->business_hours_end
            ?: config('appointments.business_hours_end', '17:00');
        $businessDays = $organization->business_days
            ?: config('appointments.business_days', [1, 2, 3, 4, 5]);

        $holidays = OrganizationHoliday::where('organization_id', $organization->id)
            ->where('date', '>=', Carbon::now()->startOfYear()->subYear())
            ->orderBy('date')
            ->get();

        return view('pages.organization.settings', [
            'organization' => $organization,
            'slotMinutes' => $slotMinutes,
            'businessHoursStart' => $businessHoursStart,
            'businessHoursEnd' => $businessHoursEnd,
            'businessDays' => $businessDays,
            'holidays' => $holidays,
        ]);
    }
}
