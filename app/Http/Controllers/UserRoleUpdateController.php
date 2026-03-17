<?php

namespace App\Http\Controllers;

use App\Concerns\AuditsPhiAccess;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserRoleUpdateController extends Controller
{
    use AuditsPhiAccess;

    public function __invoke(Request $request, User $user): RedirectResponse
    {
        $organization = Organization::findOrFail($request->input('organization_id'));

        $this->authorize('updateRole', [$user, $organization]);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,staff'],
        ]);

        $newRole = Role::where('name', $validated['role'])->firstOrFail();

        $user->roles()->detach(
            Role::whereIn('name', ['admin', 'staff'])->pluck('id')
        );
        $user->roles()->attach($newRole->id);

        $this->auditPhi('update', $user);

        return redirect()
            ->route('users.index')
            ->with('status', __('Role updated.'));
    }
}
