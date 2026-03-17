<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;

interface TableDefinition
{
    /**
     * Column definitions: key, label, sortable, align, view (optional custom cell view).
     *
     * @return array<int, array{key: string, label: string, sortable?: bool, align?: string, view?: string}>
     */
    public function columns(): array;

    /**
     * Apply search to the query.
     */
    public function applySearch(Builder $query, string $search): void;

    /**
     * Get the base query for the table.
     */
    public function query(): Builder;

    /**
     * Filter definitions for the table (optional).
     *
     * @return array<int, array{key: string, label: string, options: array<string, string>}>
     */
    public function filters(): array;

    /**
     * Apply a filter to the query.
     */
    public function applyFilter(Builder $query, string $key, mixed $value): void;

    /**
     * Message to show when the table has no rows.
     */
    public function emptyMessage(): string;

    /**
     * CSV export: headers for the first row.
     *
     * @return array<int, string>
     */
    public function exportHeaders(): array;

    /**
     * CSV export: row data as associative array (keys match header order).
     *
     * @param \Illuminate\Database\Eloquent\Model $row
     * @return array<int, string>
     */
    public function exportRowToArray($row): array;
}
