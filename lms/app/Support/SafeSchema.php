<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SafeSchema
{
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
}
