<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SafeSchema
{
    /** @var array<string, bool> */
    private static array $memory = [];

    public static function addColumn(string $table, string $column, Closure $definition): void
    {
        if (!Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($definition) {
            $definition($blueprint);
        });
    }

    public static function addIndex(string $table, array|string $columns, string $name): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columns = (array) $columns;
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        $exists = collect(DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$name]))->isNotEmpty();
        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                $blueprint->index($columns, $name);
            });
        } catch (\Throwable $e) {
            // Index already exists under another name, or the engine rejected it.
        }
    }

    public static function tableExists(string $table): bool
    {
        $key = 'schema.table.'.$table;
        if (array_key_exists($key, self::$memory)) {
            return self::$memory[$key];
        }

        try {
            return self::$memory[$key] = Cache::remember($key, 86400, fn () => Schema::hasTable($table));
        } catch (\Throwable $e) {
            return self::$memory[$key] = Schema::hasTable($table);
        }
    }

    public static function columnExists(string $table, string $column): bool
    {
        $key = 'schema.column.'.$table.'.'.$column;
        if (array_key_exists($key, self::$memory)) {
            return self::$memory[$key];
        }

        try {
            return self::$memory[$key] = Cache::remember($key, 86400, fn () => Schema::hasColumn($table, $column));
        } catch (\Throwable $e) {
            return self::$memory[$key] = Schema::hasColumn($table, $column);
        }
    }
}
