<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Patient can view their own record. Physician can view if assigned to this patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        if ($user->hasRole('patient') && $user->patient?->id === $patient->id) {
            return true;
        }

        if ($user->hasRole('physician') && $user->physician?->patients()->where('patients.id', $patient->id)->exists()) {
            return true;
        }

        if ($user->hasRole('staff')) {
            $orgIds = $user->organizations()->pluck('organizations.id')->all();

            return $patient->physicians()
                ->whereHas('organizations', fn ($q) => $q->whereIn('organizations.id', $orgIds))
                ->exists();
        }

        return false;
    }

    /**
     * Only the patient (their own record) can update their profile.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->hasRole('patient') && $user->patient?->id === $patient->id;
    }

    /**
     * Only the patient can delete their own linkage (or admin—add when you have admin).
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasRole('patient') && $user->patient?->id === $patient->id;
    }
}
