<?php

namespace App\DataTables;

use Illuminate\Support\Facades\App;

class DataTableRegistry
{
    protected static array $tables = [
        'users' => UsersTableDefinition::class,
    ];

    public static function get(string $key): TableDefinition
    {
        $class = self::$tables[$key] ?? null;

        if (! $class) {
            throw new \InvalidArgumentException("Unknown data table: {$key}");
        }

        return App::make($class);
    }

    public static function register(string $key, string $definitionClass): void
    {
        self::$tables[$key] = $definitionClass;
    }
}
