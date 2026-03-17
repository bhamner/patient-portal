# Data Tables

Reusable Livewire data table component with column sorting, filters, pagination, and search.

## Usage

### 1. Create a Table Definition

Implement `App\DataTables\TableDefinition`:

```php
<?php

namespace App\DataTables;

use App\Models\YourModel;
use Illuminate\Database\Eloquent\Builder;

class YourTableDefinition implements TableDefinition
{
    public function columns(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'custom', 'label' => 'Custom', 'view' => 'livewire.data-table.cells.your-custom-cell'],
        ];
    }

    public function query(): Builder
    {
        return YourModel::query();
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
                'key' => 'status',
                'label' => 'Status',
                'options' => [
                    '' => 'All',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ],
            ],
        ];
    }

    public function applyFilter(Builder $query, string $key, mixed $value): void
    {
        if ($key === 'status' && $value) {
            $query->where('status', $value);
        }
    }

    public function emptyMessage(): string
    {
        return 'No results';
    }

    public function exportHeaders(): array
    {
        return ['Name', 'Email', 'Status'];
    }

    public function exportRowToArray($row): array
    {
        return [$row->name, $row->email, $row->status];
    }
}
```

### 2. Register the Table

In `App\DataTables\DataTableRegistry`:

```php
protected static array $tables = [
    'users' => UsersTableDefinition::class,
    'your-table' => YourTableDefinition::class,
];
```

### 3. Use the Component

```blade
@livewire(\App\Livewire\DataTable::class, [
    'table' => 'your-table',
    'perPage' => 15,
    'context' => ['key' => 'value'],
    'scrollToId' => 'table-container',
])
```

## Column Options

- `key` - Data attribute or relationship path
- `label` - Column header text
- `sortable` - Enable sorting (default: false)
- `align` - start, center, end
- `view` - Custom Blade view for cell rendering (receives `$row`, `$value`, `$context`)
