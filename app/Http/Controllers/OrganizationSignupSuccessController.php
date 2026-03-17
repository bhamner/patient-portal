<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrganizationSignupSuccessController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('pages.organizations.signup-success', [
            'trial_days' => config('billing.trial_days', 14),
        ]);
    }
}
