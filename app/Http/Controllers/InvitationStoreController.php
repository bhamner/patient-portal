<?php

namespace App\Http\Controllers;

use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\Organization;
use App\Services\SmsSenderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationStoreController extends Controller
{
    public function __invoke(Request $request, SmsSenderInterface $sms): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'role' => ['required', 'in:patient,physician,staff,admin'],
            'email' => ['nullable', 'email', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'required_without:email'],
            'send_via' => ['required', 'in:email,sms,both'],
        ]);

        $this->authorize('createInvite', Organization::findOrFail($validated['organization_id']));

        $email = $validated['email'] ?? null;
        $phone = $validated['phone'] ?? null;
        if ($validated['send_via'] === 'email' && ! $email) {
            return redirect()->back()->withErrors(['email' => __('Email is required when sending via email.')])->withInput();
        }
        if (in_array($validated['send_via'], ['sms', 'both'], true) && ! $phone) {
            return redirect()->back()->withErrors(['phone' => __('Phone is required when sending via SMS.')])->withInput();
        }

        $invite = Invite::create([
            'email' => $email,
            'phone' => $phone,
            'token' => Invite::generateToken(),
            'role' => $validated['role'],
            'organization_id' => $validated['organization_id'],
            'inviter_user_id' => $request->user()->id,
            'expires_at' => now()->addDays(7),
        ]);

        $acceptUrl = route('register', ['token' => $invite->token]);

        if ($validated['send_via'] === 'email' && $email) {
            Mail::to($email)->send(new InviteMail($invite, $acceptUrl));
        }
        if (in_array($validated['send_via'], ['sms', 'both'], true) && $phone) {
            $message = __('You\'re invited to :app. Sign up: :url', [
                'app' => config('app.name'),
                'url' => $acceptUrl,
            ]);
            $sms->send($phone, $message);
        }

        return redirect()->route('invitations.create')->with('status', __('Invitation created and sent.'));
    }
}
