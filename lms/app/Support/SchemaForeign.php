<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaForeign
{
    public static function add(Blueprint $table, string $column, string $relatedTable, string $onDelete = 'cascade'): void
    {
        if (Schema::hasTable($relatedTable)) {
            $table->foreign($column)->references('id')->on($relatedTable)->onDelete($onDelete);
        }
    }

    public static function ensure(string $table, string $column, string $relatedTable, string $onDelete = 'cascade'): void
    {
        if (!Schema::hasTable($table) || !Schema::hasTable($relatedTable)) {
            return;
        }

        $constraint = "{$table}_{$column}_foreign";
        $exists = collect(DB::select(
            'select CONSTRAINT_NAME from information_schema.TABLE_CONSTRAINTS
             where CONSTRAINT_SCHEMA = ? and TABLE_NAME = ? and CONSTRAINT_NAME = ? and CONSTRAINT_TYPE = ?',
            [DB::getDatabaseName(), $table, $constraint, 'FOREIGN KEY']
        ))->isNotEmpty();

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $relatedTable, $onDelete) {
            $blueprint->foreign($column)->references('id')->on($relatedTable)->onDelete($onDelete);
        });
    }
}
