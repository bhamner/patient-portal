@props(['row', 'value', 'context' => []])

@php
    $organization = $context['organization'] ?? null;
    $canManageRoles = $context['canManageRoles'] ?? false;
    $roles = $row->roles->pluck('name')->all();
@endphp

@if ($canManageRoles && $organization && (in_array('admin', $roles, true) || in_array('staff', $roles, true)) && $row->id !== auth()->id())
    <form method="POST" action="{{ route('users.roles.update', $row) }}" class="flex items-center gap-2">
        @csrf
        @method('PUT')
        <input type="hidden" name="organization_id" value="{{ $organization->id }}">
        <flux:select
            name="role"
            onchange="this.form.submit()"
            class="text-sm"
        >
            <option value="admin" @selected(in_array('admin', $roles, true))>{{ __('Admin') }}</option>
            <option value="staff" @selected(!in_array('admin', $roles, true))>{{ __('Staff') }}</option>
        </flux:select>
    </form>
@endif
