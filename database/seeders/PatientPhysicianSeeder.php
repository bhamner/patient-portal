<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;

class PatientPhysicianSeeder extends Seeder
{
    /**
     * Backfill patient_physician from existing appointments.
     * Ensures every patient who has an appointment with a physician
     * is linked via the patient_physician pivot table.
     */
    public function run(): void
    {
        $pairs = Appointment::query()
            ->whereNotNull('patient_id')
            ->whereNotNull('physician_id')
            ->select('patient_id', 'physician_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            $pair->patient->physicians()->syncWithoutDetaching([$pair->physician_id]);
        }
    }
}
