<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class UserPolicy
{
    /**
     * Users with can_view_users can list org users (staff + physicians).
     */
    public function viewAny(User $user, Organization $organization): bool
    {
        return $user->canPerform('can_view_users')
            && $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Admins can update roles of users in their org. Staff cannot.
     */
    public function updateRole(User $actor, User $target, Organization $organization): bool
    {
        if (! $actor->canPerform('can_manage_roles')) {
            return false;
        }

        if (! $actor->organizations()->where('organizations.id', $organization->id)->exists()) {
            return false;
        }

        return $this->targetBelongsToOrg($target, $organization);
    }

    private function targetBelongsToOrg(User $target, Organization $organization): bool
    {
        if ($target->hasRole('staff') && $target->organizations()->where('organizations.id', $organization->id)->exists()) {
            return true;
        }

        if ($target->hasRole('physician') && $target->physician?->organizations()->where('organizations.id', $organization->id)->exists()) {
            return true;
        }

        return false;
    }
}
