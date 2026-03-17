<?php

namespace App\Http\Controllers;

use App\Concerns\AuditsPhiAccess;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UsersIndexController extends Controller
{
    use AuditsPhiAccess;

    public function __invoke(): View
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        $this->authorize('viewAny', [User::class, $organization]);
        $this->auditPhi('view', $organization);

        $pendingInvites = $organization->invites()
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.users.index', [
            'organization' => $organization,
            'pendingInvites' => $pendingInvites,
            'canInvite' => request()->user()->canPerform('can_invite'),
            'canManageRoles' => request()->user()->canPerform('can_manage_roles'),
        ]);
    }
}
