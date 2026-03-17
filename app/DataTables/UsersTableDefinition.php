<?php

namespace App\DataTables;

use App\Models\Organization;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UsersTableDefinition implements TableDefinition
{
    public function __construct(
        protected Organization $organization
    ) {}

    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
            ['key' => 'email', 'label' => __('Email'), 'sortable' => true],
            ['key' => 'roles', 'label' => __('Roles'), 'sortable' => false, 'view' => 'livewire.data-table.cells.roles'],
            ['key' => 'updated_at', 'label' => __('Last updated'), 'sortable' => true, 'view' => 'livewire.data-table.cells.date'],
            ['key' => 'last_visit_at', 'label' => __('Last visit'), 'sortable' => true, 'view' => 'livewire.data-table.cells.date'],
            ['key' => 'actions', 'label' => '', 'sortable' => false, 'align' => 'end', 'view' => 'livewire.data-table.cells.user-actions'],
        ];
    }

    public function query(): Builder
    {
        $staffIds = $this->organization->staff()->pluck('users.id');
        $physicianUserIds = $this->organization->physicians()->get()->pluck('user_id')->filter();
        $orgPhysicianIds = $this->organization->physicians()->pluck('physicians.id');
        $patientUserIds = Patient::whereHas('physicians', fn ($q) => $q->whereIn('physicians.id', $orgPhysicianIds))
            ->pluck('user_id');

        $userIds = $staffIds->concat($physicianUserIds)->concat($patientUserIds)->unique()->values();

        $orgId = $this->organization->id;

        return User::query()
            ->selectRaw(
                'users.*, (
                    SELECT MAX(a.starts_at)
                    FROM appointments a
                    INNER JOIN patients p ON a.patient_id = p.id AND p.user_id = users.id
                    WHERE a.organization_id = ?
                ) as last_visit_at',
                [$orgId]
            )
            ->whereIn('id', $userIds)
            ->with(['roles']);
    }

    public function applySearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function filters(): array
    {
        return [
            [
                'key' => 'role',
                'label' => __('Role'),
                'options' => [
                    '' => __('All roles'),
                    'admin' => __('Admin'),
                    'staff' => __('Staff'),
                    'physician' => __('Physician'),
                    'patient' => __('Patient'),
                ],
            ],
        ];
    }

    public function applyFilter(Builder $query, string $key, mixed $value): void
    {
        if ($key === 'role' && $value !== '') {
            $query->whereHas('roles', fn ($q) => $q->where('name', $value));
        }
    }

    public function emptyMessage(): string
    {
        return __('No users');
    }

    public function exportHeaders(): array
    {
        return [
            __('Name'),
            __('Email'),
            __('Roles'),
            __('Last updated'),
            __('Last visit'),
        ];
    }

    public function exportRowToArray($row): array
    {
        $roles = $row->roles->pluck('name')->map(fn ($r) => __(ucfirst($r)))->implode(', ');
        $updatedAt = $row->updated_at ? $row->updated_at->format('Y-m-d H:i') : '';
        $lastVisit = $row->last_visit_at
            ? \Carbon\Carbon::parse($row->last_visit_at)->format('Y-m-d H:i')
            : '';

        return [
            $row->name,
            $row->email,
            $roles,
            $updatedAt,
            $lastVisit,
        ];
    }
}
