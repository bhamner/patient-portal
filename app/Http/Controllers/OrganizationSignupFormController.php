<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class OrganizationSignupFormController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.organizations.signup');
    }
}
