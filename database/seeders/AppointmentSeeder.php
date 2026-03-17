<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::firstOrCreate(
            ['name' => 'Demo Practice'],
            []
        );

        $patientRole = Role::where('name', RoleSeeder::PATIENT)->firstOrFail();
        $physicianRole = Role::where('name', RoleSeeder::PHYSICIAN)->firstOrFail();

        // Create three physicians
        $physicians = collect([
            ['name' => 'Dr. Adams', 'email' => 'dr.adams@example.com'],
            ['name' => 'Dr. Baker', 'email' => 'dr.baker@example.com'],
            ['name' => 'Dr. Clark', 'email' => 'dr.clark@example.com'],
        ])->map(function (array $data) use ($physicianRole) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password')]
            );
            $user->roles()->syncWithoutDetaching([$physicianRole->id]);

            return Physician::firstOrCreate(['user_id' => $user->id]);
        });

        // Create six patients
        $patients = collect([
            ['name' => 'Alice Patient', 'email' => 'alice@example.com'],
            ['name' => 'Bob Patient', 'email' => 'bob@example.com'],
            ['name' => 'Carol Patient', 'email' => 'carol@example.com'],
            ['name' => 'Dave Patient', 'email' => 'dave@example.com'],
            ['name' => 'Eve Patient', 'email' => 'eve@example.com'],
            ['name' => 'Frank Patient', 'email' => 'frank@example.com'],
        ])->map(function (array $data) use ($patientRole) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password')]
            );
            $user->roles()->syncWithoutDetaching([$patientRole->id]);

            return Patient::firstOrCreate(['user_id' => $user->id]);
        });

        // Attach physicians to organization
        $organization->physicians()->syncWithoutDetaching($physicians->pluck('id')->all());

        // Create overlapping appointments for the next few days
        $startBase = Carbon::now()->startOfDay()->addHours(9); // 9:00 today
        $slots = [0, 30, 60]; // 09:00, 09:30, 10:00

        for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
            foreach ($slots as $slotIndex => $minutes) {
                foreach ($physicians as $physicianIndex => $physician) {
                    // Rotate patients so each physician sees different people
                    $patient = $patients[($physicianIndex + $slotIndex) % $patients->count()];

                    $starts = (clone $startBase)->addDays($dayOffset)->addMinutes($minutes);
                    $ends = (clone $starts)->addMinutes(30);

                    Appointment::updateOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'patient_id' => $patient->id,
                            'physician_id' => $physician->id,
                            'starts_at' => $starts,
                        ],
                        [
                            'ends_at' => $ends,
                            'title' => 'Follow-up',
                            'status' => 'scheduled',
                            'notes' => 'Demo seeded appointment.',
                        ]
                    );

                    $patient->physicians()->syncWithoutDetaching([$physician->id]);
                }
            }
        }
    }
}

