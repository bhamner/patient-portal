<?php

namespace App\Http\Controllers;

use App\Actions\OrganizationSignup;
use App\Concerns\PasswordValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationSignupStoreController extends Controller
{
    use PasswordValidationRules;

    public function __invoke(Request $request, OrganizationSignup $signup): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', 'unique:organizations,subdomain'],
            'primary_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'admins' => ['required', 'array'],
        ];
        $attributes = [];
        foreach ($request->input('admins', []) as $i => $admin) {
            $hasName = ! empty(trim($admin['name'] ?? ''));
            $hasEmail = ! empty(trim($admin['email'] ?? ''));
            if (! $hasName && ! $hasEmail) {
                continue;
            }
            $rules["admins.{$i}.name"] = ['required', 'string', 'max:255'];
            $rules["admins.{$i}.email"] = ['required', 'string', 'email', 'max:255'];
            $rules["admins.{$i}.password"] = $this->passwordRules();
            $attributes["admins.{$i}.name"] = __('administrator name');
            $attributes["admins.{$i}.email"] = __('administrator email');
            $attributes["admins.{$i}.password"] = __('administrator password');
        }

        $validated = $request->validate($rules, [], $attributes);

        // Normalise colors to #RRGGBB
        foreach (['primary_color', 'secondary_color', 'accent_color'] as $colorKey) {
            if (! empty($validated[$colorKey] ?? null)) {
                $value = ltrim($validated[$colorKey], '#');
                $validated[$colorKey] = '#'.$value;
            }
        }

        $validated['admins'] = collect($validated['admins'] ?? [])->filter(fn ($a) => ! empty(trim($a['name'] ?? '')))->values()->all();
        if (count($validated['admins']) < 1) {
            return redirect()->back()->withErrors(['admins' => [__('Add at least one administrator.')]])->withInput();
        }
        if (count($validated['admins']) > 2) {
            return redirect()->back()->withErrors(['admins' => [__('You may add at most two administrators.')]])->withInput();
        }

        $organization = $signup->signup($validated);

        $priceId = config('billing.price_id');
        if (! $priceId) {
            return redirect()->route('login')->with('status', __('Organization created. You can log in with the administrator email and password you chose.'));
        }

        $trialDays = config('billing.trial_days', 14);
        $checkout = $organization->newSubscription('default', $priceId)
            ->trialDays($trialDays)
            ->checkout([
                'success_url' => route('organization.signup.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('organization.signup'),
            ], [
                'name' => $organization->name,
            ]);

        return redirect($checkout->url);
    }
}
