<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class OrganizationBrandingController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Organization|null $organization */
        $organization = app()->has(Organization::class) ? app(Organization::class) : null;

        if (! $organization) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => [
                'nullable',
                File::types(['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])
                    ->max(2 * 1024), // 2MB
            ],
            'primary_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
            'accent_color' => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{6}$/'],
        ]);

        $data = [
            'name' => $validated['name'],
            'primary_color' => $this->normalizeHex($validated['primary_color'] ?? null),
            'secondary_color' => $this->normalizeHex($validated['secondary_color'] ?? null),
            'accent_color' => $this->normalizeHex($validated['accent_color'] ?? null),
        ];

        if ($request->hasFile('logo')) {
            $dir = "organization-logos/{$organization->id}";
            Storage::disk('public')->deleteDirectory($dir);

            $file = $request->file('logo');
            $ext = $file->getClientOriginalExtension();
            $path = $file->storeAs($dir, "logo.{$ext}", 'public');

            $data['logo_url'] = $path;
        }

        if ($request->boolean('remove_logo')) {
            if ($organization->logo_url) {
                Storage::disk('public')->delete($organization->logo_url);
            }
            $data['logo_url'] = null;
        }

        $organization->update($data);

        return redirect()
            ->route('organization.settings')
            ->with('status', __('Organization branding updated.'));
    }

    private function normalizeHex(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = ltrim($value, '#');

        return strlen($value) === 6 ? "#{$value}" : null;
    }
}
