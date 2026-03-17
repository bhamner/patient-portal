<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Organization;
use App\Models\OrganizationHoliday;
use App\Models\Patient;
use App\Models\Physician;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppointmentCalendarController extends Controller
{
    /**
     * Calculate slots taken and available per day.
     *
     * @param  array<int>  $businessDays  ISO day of week (1=Mon, 7=Sun)
     * @param  array<string, string>  $holidaysByDate  date => name (empty string if no name)
     * @return array<string, array{total: int, taken: int, available: int, label?: string}>
     */
    private function calculateSlotsByDay(
        Carbon $startOfMonth,
        int $daysInMonth,
        Collection $appointmentsByDay,
        int $slotMinutes,
        string $workingStart,
        string $workingEnd,
        array $businessDays,
        array $holidaysByDate = []
    ): array {
        [$startHour, $startMin] = array_map('intval', explode(':', $workingStart));
        [$endHour, $endMin] = array_map('intval', explode(':', $workingEnd));
        $dayStartMinutes = $startHour * 60 + $startMin;
        $dayEndMinutes = $endHour * 60 + $endMin;
        $workingMinutes = $dayEndMinutes - $dayStartMinutes;
        $totalSlotsPerDay = (int) floor($workingMinutes / $slotMinutes);

        $slotsByDay = [];
        $carbonStart = $startOfMonth->copy();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $carbonStart->copy()->day($day);
            $dateKey = $date->toDateString();
            $dayOfWeek = (int) $date->isoFormat('E');

            if (isset($holidaysByDate[$dateKey])) {
                $slotsByDay[$dateKey] = [
                    'total' => 0,
                    'taken' => 0,
                    'available' => 0,
                    'label' => $holidaysByDate[$dateKey] ?: null,
                ];
                continue;
            }

            if (! in_array($dayOfWeek, $businessDays, true)) {
                $slotsByDay[$dateKey] = ['total' => 0, 'taken' => 0, 'available' => 0];
                continue;
            }

            $dayStart = $date->copy()->setTimeFromTimeString($workingStart);
            $dayEnd = $date->copy()->setTimeFromTimeString($workingEnd);

            $occupiedSlotIndices = [];
            $dayAppointments = $appointmentsByDay->get($dateKey, collect());

            foreach ($dayAppointments as $appointment) {
                $starts = $appointment->starts_at;
                $ends = $appointment->ends_at ?? $starts->copy()->addMinutes($slotMinutes);

                if ($starts->lt($dayStart)) {
                    $starts = $dayStart->copy();
                }
                if ($ends->gt($dayEnd)) {
                    $ends = $dayEnd->copy();
                }
                if ($starts->gte($ends)) {
                    continue;
                }

                $startMinutes = $dayStart->diffInMinutes($starts);
                $endMinutes = $dayStart->diffInMinutes($ends);
                $firstSlot = (int) floor($startMinutes / $slotMinutes);
                $numSlots = (int) ceil(($endMinutes - $startMinutes) / $slotMinutes);

                for ($i = 0; $i < $numSlots; $i++) {
                    $idx = $firstSlot + $i;
                    if ($idx >= 0 && $idx < $totalSlotsPerDay) {
                        $occupiedSlotIndices[$idx] = true;
                    }
                }
            }

            $taken = count($occupiedSlotIndices);
            $slotsByDay[$dateKey] = [
                'total' => $totalSlotsPerDay,
                'taken' => $taken,
                'available' => max(0, $totalSlotsPerDay - $taken),
            ];
        }

        return $slotsByDay;
    }

    public function __invoke(Request $request): View
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;
        $user = $request->user();

        $isStaff = $user->hasRole('staff');
        $isPhysician = $user->hasRole('physician');
        $isPatient = $user->hasRole('patient');

        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        $query = Appointment::query()
            ->with(['patient.user', 'physician.user'])
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->orderBy('starts_at');

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        // Role-based visibility (staff gets all; physician filter is client-side)
        if ($isPatient && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        } elseif ($isPhysician && $user->physician) {
            $query->where('physician_id', $user->physician->id);
        }

        $appointments = $query->get()->groupBy(function (Appointment $appointment) {
            return $appointment->getDayKey();
        });

        $physicians = collect();
        if ($isStaff) {
            $physicians = Physician::with('user')->get()->sortBy(fn (Physician $p) => $p->user?->name ?? '');
        }

        $patients = Patient::with('user')->get()->sortBy(fn (Patient $p) => $p->user?->name ?? '');

        $appointmentsByDayJson = [];
        foreach ($appointments as $dateKey => $dayAppointments) {
            $appointmentsByDayJson[$dateKey] = $dayAppointments->map(fn ($a) => [
                'id' => $a->id,
                'time' => $a->starts_at->format('H:i'),
                'patient_id' => $a->patient_id,
                'patient_name' => optional(optional($a->patient)->user)->name,
                'physician_id' => $a->physician_id,
                'physician_name' => optional(optional($a->physician)->user)->name,
                'title' => $a->title,
                'status' => $a->status,
                'starts_at' => $a->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => optional($a->ends_at)?->format('Y-m-d\TH:i'),
                'notes' => $a->notes,
                'edit_url' => route('appointments.update', $a),
                'delete_url' => route('appointments.destroy', $a),
            ])->values()->all();
        }

        $slotMinutes = $organization && $organization->appointment_slot_minutes
            ? (int) $organization->appointment_slot_minutes
            : (int) config('appointments.slot_minutes', 30);
        $slotMinutes = in_array($slotMinutes, Organization::SLOT_OPTIONS, true) ? $slotMinutes : 30;

        $workingStart = $organization && $organization->business_hours_start
            ? $organization->business_hours_start
            : config('appointments.business_hours_start', '08:00');
        $workingEnd = $organization && $organization->business_hours_end
            ? $organization->business_hours_end
            : config('appointments.business_hours_end', '17:00');
        $businessDays = $organization && $organization->business_days
            ? $organization->business_days
            : config('appointments.business_days', [1, 2, 3, 4, 5]);

        $holidaysByDate = [];
        if ($organization) {
            $holidays = OrganizationHoliday::query()
                ->where('organization_id', $organization->id)
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->orWhere('recurring', true);
                })
                ->get();
            $carbonStart = $startOfMonth->copy();
            for ($day = 1; $day <= $startOfMonth->daysInMonth; $day++) {
                $date = $carbonStart->copy()->day($day);
                $dateKey = $date->toDateString();
                $monthDay = $date->format('m-d');
                foreach ($holidays as $h) {
                    $matches = $h->date->toDateString() === $dateKey
                        || ($h->recurring && $h->date->format('m-d') === $monthDay);
                    if ($matches) {
                        $holidaysByDate[$dateKey] = $h->name ?? '';
                        break;
                    }
                }
            }
        }

        $slotsByDay = $this->calculateSlotsByDay(
            $startOfMonth,
            $daysInMonth = $startOfMonth->daysInMonth,
            $appointments,
            $slotMinutes,
            $workingStart,
            $workingEnd,
            $businessDays,
            $holidaysByDate
        );

        return view('pages.appointments.calendar', [
            'month' => $month,
            'year' => $year,
            'startOfMonth' => $startOfMonth,
            'endOfMonth' => $endOfMonth,
            'appointmentsByDay' => $appointments,
            'appointmentsByDayJson' => $appointmentsByDayJson,
            'slotsByDay' => $slotsByDay,
            'slotMinutes' => $slotMinutes,
            'workingStart' => $workingStart,
            'workingEnd' => $workingEnd,
            'patients' => $patients,
            'physicians' => $physicians,
            'physiciansJson' => $physicians->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->user?->name ?? __('Physician #:id', ['id' => $p->id]),
            ])->values()->all(),
            'patientsJson' => $patients->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->user?->name ?? __('Patient #:id', ['id' => $p->id]),
            ])->values()->all(),
            'isStaff' => $isStaff,
            'isPhysician' => $isPhysician,
            'isPatient' => $isPatient,
            'organization' => $organization,
            'storeUrl' => route('appointments.store'),
        ]);
    }
}

