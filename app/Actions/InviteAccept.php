<?php

namespace App\Actions;

use App\Models\Invite;
use App\Models\Organization;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InviteAccept
{
    /**
     * Create user from a valid invite, assign role, link to org/patient/physician, mark invite used.
     *
     * @param  array{name: string, email: string, password: string}  $input
     */
    public function accept(Invite $invite, array $input): User
    {
        if (! $invite->isValid()) {
            throw ValidationException::withMessages([
                'token' => [__('This invitation is no longer valid.')],
            ]);
        }

        $email = $input['email'];
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => [__('An account with this email already exists. Please log in.')],
            ]);
        }

        return DB::transaction(function () use ($invite, $input, $email) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $email,
                'password' => $input['password'],
            ]);

            $role = Role::where('name', $invite->role)->firstOrFail();
            $user->roles()->attach($role->id);

            $organization = $invite->organization;

            match ($invite->role) {
                'patient' => $this->linkPatient($user),
                'physician' => $this->linkPhysician($user, $organization),
                'staff', 'admin' => $this->linkStaff($user, $organization),
                default => null,
            };

            $invite->update(['used_at' => now()]);

            return $user;
        });
    }

    private function linkPatient(User $user): void
    {
        Patient::create(['user_id' => $user->id]);
    }

    private function linkPhysician(User $user, Organization $organization): void
    {
        $physician = Physician::create(['user_id' => $user->id]);
        $physician->organizations()->attach($organization->id);
    }

    private function linkStaff(User $user, Organization $organization): void
    {
        $organization->staff()->attach($user->id);
    }
}
