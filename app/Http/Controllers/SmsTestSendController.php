<?php

namespace App\Http\Controllers;

use App\Services\SmsSenderInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SmsTestSendController extends Controller
{
    public function __invoke(Request $request, SmsSenderInterface $sms): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'string'],
            'message' => ['required', 'string', 'max:160'],
        ]);

        $sms->send($validated['to'], $validated['message']);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('sms.test')->with('status', __('SMS sent successfully.'));
    }
}
