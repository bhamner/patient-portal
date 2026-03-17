<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Organization;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // Demo organization for local development
        $organization = Organization::firstOrCreate(
            ['name' => 'Demo Practice'],
            []
        );

        $adminRole = Role::where('name', RoleSeeder::ADMIN)->first();
        $staffRole = Role::where('name', RoleSeeder::STAFF)->first();

        // Staff admin (first user gets admin role)
        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Admin', 'password' => bcrypt('password')]
        );
        $staff->roles()->sync([($adminRole ?? $staffRole)->id]);
        $organization->staff()->syncWithoutDetaching([$staff->id]);

        $this->call(AppointmentSeeder::class);
        $this->call(PatientPhysicianSeeder::class);
        $this->call(OrganizationHolidaySeeder::class);
    }
}
