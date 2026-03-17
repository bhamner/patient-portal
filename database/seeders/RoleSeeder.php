<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Default role names.
     */
    public const ADMIN = 'admin';

    public const PATIENT = 'patient';

    public const PHYSICIAN = 'physician';

    public const STAFF = 'staff';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            self::ADMIN,
            self::STAFF,
            self::PHYSICIAN,
            self::PATIENT,
        ];

        foreach ($roles as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
