<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Staff and physicians belonging to this org can view it.
     */
    public function view(User $user, Organization $organization): bool
    {
        if ($user->hasRole('staff') && $user->organizations()->where('organizations.id', $organization->id)->exists()) {
            return true;
        }

        if ($user->hasRole('physician') && $user->physician?->organizations()->where('organizations.id', $organization->id)->exists()) {
            return true;
        }

        return false;
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasRole('staff') && $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->hasRole('staff') && $user->organizations()->where('organizations.id', $organization->id)->exists();
    }

    /**
     * Users with can_invite roles (admin, staff) who belong to this org can create invites.
     * When billing is enforced (config billing.invites_require_subscription), org must be subscribed or on trial.
     */
    public function createInvite(User $user, Organization $organization): bool
    {
        if (! $user->canPerform('can_invite') || ! $user->organizations()->where('organizations.id', $organization->id)->exists()) {
            return false;
        }

        if (config('billing.invites_require_subscription', false)) {
            return $organization->subscribed('default') || $organization->onTrial('default');
        }

        return true;
    }
}
