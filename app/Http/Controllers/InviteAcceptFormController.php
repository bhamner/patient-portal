<?php

namespace App\Http\Controllers;

use App\Models\Invite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InviteAcceptFormController extends Controller
{
    public function __invoke(Request $request): View
    {
        $token = $request->query('token');

        if (! $token) {
            return view('pages.auth.register-invite-only');
        }

        $invite = Invite::where('token', $token)->first();

        if (! $invite || ! $invite->isValid()) {
            return view('pages.auth.register-invite-only', [
                'error' => __('This invitation link is invalid or has expired.'),
            ]);
        }

        return view('pages.auth.register', [
            'invite' => $invite,
            'token' => $token,
        ]);
    }
}
