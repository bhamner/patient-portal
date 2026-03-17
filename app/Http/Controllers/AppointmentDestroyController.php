<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;

class AppointmentDestroyController extends Controller
{
    public function __invoke(Appointment $appointment): RedirectResponse
    {
        $appointment->delete();

        return redirect()->route('appointments.calendar')
            ->with('status', __('Appointment deleted.'));
    }
}

