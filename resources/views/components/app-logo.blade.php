@props([
    'sidebar' => false,
])

@php
    $organization = $currentOrganization ?? (app()->has(\App\Models\Organization::class) ? app(\App\Models\Organization::class) : null);
    $brandName = $organization?->name ?? config('app.name');
    $logoUrl = $organization?->logo_url
        ? \Illuminate\Support\Facades\Storage::url($organization->logo_url)
        : null;
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$brandName" :logo="$logoUrl" {{ $attributes }}>
        @unless($logoUrl)
            <x-slot name="logo">
                <x-app-logo-icon class="size-7" aria-hidden="true" />
            </x-slot>
        @endunless
    </flux:sidebar.brand>
@else
    <flux:brand :name="$brandName" :logo="$logoUrl" {{ $attributes }}>
        @unless($logoUrl)
            <x-slot name="logo">
                <x-app-logo-icon class="size-7" aria-hidden="true" />
            </x-slot>
        @endunless
    </flux:brand>
@endif
