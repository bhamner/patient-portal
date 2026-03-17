<?php

namespace App\Http\Controllers;

use App\Actions\InviteAccept;
use App\Concerns\PasswordValidationRules;
use App\Models\Invite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InviteAcceptStoreController extends Controller
{
    use PasswordValidationRules;

    public function __invoke(Request $request, InviteAccept $accept): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => $this->passwordRules(),
        ]);

        $invite = Invite::where('token', $validated['token'])->first();

        if (! $invite) {
            return redirect()->route('register')->withErrors(['token' => __('Invalid invitation.')]);
        }

        $accept->accept($invite, $validated);

        return redirect()->route('login')->with('status', __('Account created. Please log in.'));
    }
}
