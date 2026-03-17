<?php

namespace App\Policies;

use App\Models\Physician;
use App\Models\User;

class PhysicianPolicy
{
    /**
     * Physician can view self. Patient can view if assigned to this physician. Staff can view if physician belongs to their org.
     */
    public function view(User $user, Physician $physician): bool
    {
        if ($user->hasRole('physician') && $user->physician?->id === $physician->id) {
            return true;
        }

        if ($user->hasRole('patient') && $user->patient?->physicians()->where('physicians.id', $physician->id)->exists()) {
            return true;
        }

        if ($user->hasRole('staff') && $physician->organizations()->whereIn('organizations.id', $user->organizations()->pluck('organizations.id'))->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Only the physician can update their own profile.
     */
    public function update(User $user, Physician $physician): bool
    {
        return $user->hasRole('physician') && $user->physician?->id === $physician->id;
    }

    public function delete(User $user, Physician $physician): bool
    {
        return $user->hasRole('physician') && $user->physician?->id === $physician->id;
    }
}
