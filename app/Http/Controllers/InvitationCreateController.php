<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvitationCreateController extends Controller
{
    public function __invoke(Request $request): View
    {
        $organizations = $request->user()->organizations()->orderBy('name')->get();

        $currentOrg = app()->has(Organization::class) ? app(Organization::class) : null;

        return view('pages.invitations.create', [
            'organizations' => $organizations,
            'currentOrganization' => $currentOrg,
        ]);
    }
}
