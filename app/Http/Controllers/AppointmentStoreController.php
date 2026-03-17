<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Organization;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentStoreController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;
        $user = $request->user();

        $validated = $request->validate([
            'patient_id' => ['nullable', 'exists:patients,id'],
            'physician_id' => ['nullable', 'exists:physicians,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
        ]);

        // Role-based ownership
        if ($user->hasRole('patient') && $user->patient) {
            $validated['patient_id'] = $user->patient->id;
        }

        if ($user->hasRole('physician') && $user->physician) {
            $validated['physician_id'] = $user->physician->id;
        }

        $startsAt = \Carbon\Carbon::parse($validated['starts_at']);
        $endsAt = isset($validated['ends_at']) ? \Carbon\Carbon::parse($validated['ends_at']) : null;

        $slotMinutes = $organization && $organization->appointment_slot_minutes
            ? (int) $organization->appointment_slot_minutes
            : 30;

        if ($startsAt->minute % $slotMinutes !== 0 || ($endsAt && $endsAt->minute % $slotMinutes !== 0)) {
            return back()
                ->withErrors(['starts_at' => __('Appointments must start and end on :minute-minute increments.', ['minute' => $slotMinutes])])
                ->withInput();
        }

        if ($endsAt && $startsAt->diffInMinutes($endsAt) < $slotMinutes) {
            return back()
                ->withErrors(['ends_at' => __('Appointments must be at least :minute minutes long.', ['minute' => $slotMinutes])])
                ->withInput();
        }

        $appointment = Appointment::create([
            'organization_id' => $organization?->id,
            'patient_id' => $validated['patient_id'] ?? null,
            'physician_id' => $validated['physician_id'] ?? null,
            'title' => $validated['title'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($appointment->patient_id && $appointment->physician_id) {
            Patient::find($appointment->patient_id)?->physicians()->syncWithoutDetaching([$appointment->physician_id]);
        }

        return redirect()->route('appointments.calendar')
            ->with('status', __('Appointment created.'));
    }
}

