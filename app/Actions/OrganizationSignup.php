<?php

namespace App\Actions;

use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrganizationSignup
{
    /**
     * Create organization and one or more staff (admin) users. Does not start subscription.
     *
     * @param  array{
     *     name: string,
     *     subdomain?: string|null,
     *     primary_color?: string|null,
     *     secondary_color?: string|null,
     *     accent_color?: string|null,
     *     logo_url?: string|null,
     *     admins: array<int, array{name: string, email: string, password: string}>
     * }  $input
     */
    public function signup(array $input): Organization
    {
        $emails = array_column($input['admins'], 'email');
        if (User::whereIn('email', $emails)->exists()) {
            throw ValidationException::withMessages([
                'admins' => [__('One or more of these email addresses are already registered.')],
            ]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->firstOrFail();
        $roleToUse = $adminRole ?? $staffRole;

        return DB::transaction(function () use ($input, $adminRole, $staffRole) {
            $organization = Organization::create([
                'name' => $input['name'],
                'subdomain' => $input['subdomain'] ?? null,
                'primary_color' => $input['primary_color'] ?? null,
                'secondary_color' => $input['secondary_color'] ?? null,
                'accent_color' => $input['accent_color'] ?? null,
                'logo_url' => $input['logo_url'] ?? null,
            ]);

            foreach ($input['admins'] as $i => $admin) {
                $user = User::create([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'password' => $admin['password'],
                ]);
                $user->roles()->attach(($i === 0 && $adminRole ? $adminRole : $staffRole)->id);
                $organization->staff()->attach($user->id);
            }

            return $organization;
        });
    }
}
