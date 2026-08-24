<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Schema\Blueprint;
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
}
