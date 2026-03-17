<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentUpdateController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['nullable', 'exists:patients,id'],
            'physician_id' => ['nullable', 'exists:physicians,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:scheduled,completed,canceled'],
        ]);

        $appointment->update($validated);

        if ($appointment->patient_id && $appointment->physician_id) {
            Patient::find($appointment->patient_id)?->physicians()->syncWithoutDetaching([$appointment->physician_id]);
        }

        return redirect()->route('appointments.calendar')
            ->with('status', __('Appointment updated.'));
    }
}

