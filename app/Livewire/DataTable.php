<?php

namespace App\Livewire;

use App\DataTables\DataTableRegistry;
use App\DataTables\TableDefinition;
use App\Models\Organization;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public string $table;

    public string $search = '';

    public string $sortBy = '';

    public string $sortDirection = 'asc';

    public array $filters = [];

    /** @var int|string Per-page: 5, 10, 25, 50, or 'all' */
    public int|string $perPage = 10;

    /** @var mixed Optional context passed to the table (e.g. organization for users table) */
    public mixed $context = null;

    public ?string $scrollToId = null;

    public const PER_PAGE_OPTIONS = [5, 10, 25, 50, 'all'];

    public function mount(string $table, int|string $perPage = 10, mixed $context = null, ?string $scrollToId = null): void
    {
        $this->table = $table;
        $this->perPage = $perPage;
        $this->context = $context;
        $this->scrollToId = $scrollToId;

        $definition = $this->getDefinition();
        $columns = $definition->columns();
        $sortable = collect($columns)->firstWhere('sortable', true);
        $this->sortBy = $sortable ? $sortable['key'] : '';

        foreach ($definition->filters() as $filter) {
            $this->filters[$filter['key']] = $this->filters[$filter['key']] ?? '';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = array_fill_keys(array_keys($this->filters), '');
        $this->resetPage();
    }

    public function getActiveFilterCount(): int
    {
        return collect($this->filters)->filter(fn ($v) => $v !== '' && $v !== null)->count();
    }

    /**
     * @return array<int, array{key: string, filterLabel: string, valueLabel: string}>
     */
    public function getActiveFiltersWithLabels(): array
    {
        $definition = $this->getDefinition();
        $result = [];

        foreach ($definition->filters() as $filter) {
            $value = $this->filters[$filter['key']] ?? '';
            if ($value === '' || $value === null) {
                continue;
            }
            $valueLabel = $filter['options'][$value] ?? (string) $value;
            $result[] = [
                'key' => $filter['key'],
                'filterLabel' => $filter['label'],
                'valueLabel' => $valueLabel,
            ];
        }

        return $result;
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function getDefinition(): TableDefinition
    {
        // Ensure organization is in container for Livewire requests (may not run route middleware)
        if (is_array($this->context) && isset($this->context['organization']) && $this->context['organization'] instanceof Organization) {
            app()->instance(Organization::class, $this->context['organization']);
        }

        return DataTableRegistry::get($this->table);
    }

    #[Computed]
    public function rows()
    {
        $definition = $this->getDefinition();
        $query = $definition->query();

        if ($this->search !== '') {
            $definition->applySearch($query, $this->search);
        }

        foreach ($this->filters as $key => $value) {
            if ($value !== '' && $value !== null) {
                $definition->applyFilter($query, $key, $value);
            }
        }

        if ($this->sortBy !== '') {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $perPage = $this->perPage === 'all' ? 99999 : (int) $this->perPage;

        return $query->paginate($perPage);
    }

    public function downloadCsv()
    {
        $definition = $this->getDefinition();
        $query = $definition->query();

        if ($this->search !== '') {
            $definition->applySearch($query, $this->search);
        }

        foreach ($this->filters as $key => $value) {
            if ($value !== '' && $value !== null) {
                $definition->applyFilter($query, $key, $value);
            }
        }

        if ($this->sortBy !== '') {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $filename = $this->table . '-export-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($definition, $query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $definition->exportHeaders());

            $query->chunk(500, function ($rows) use ($definition, $handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, $definition->exportRowToArray($row));
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render()
    {
        return view('livewire.data-table');
    }
}
