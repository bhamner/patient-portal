<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class SmsTestFormController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.sms.test');
    }
}
