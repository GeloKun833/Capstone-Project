<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->nullEmptyStrings('users', ['email', 'user_id']);
        $this->nullEmptyStrings('students', ['email', 'user_id', 'admission_id']);
        $this->nullEmptyStrings('teachers', ['user_id']);

        $this->dedupeColumn('users', 'email');
        $this->dedupeColumn('users', 'user_id');
        $this->dedupeColumn('students', 'email');
        $this->dedupeColumn('students', 'admission_id');
        $this->dedupeColumn('teachers', 'user_id');

        Schema::table('users', function (Blueprint $table) {
            if (! $this->hasIndex('users', 'users_email_unique')) {
                $table->unique('email');
            }
            if (! $this->hasIndex('users', 'users_user_id_unique')) {
                $table->unique('user_id');
            }
        });

        if (! Schema::hasColumn('students', 'parent_user_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('parent_user_id')->nullable()->after('parent_email');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            if (! $this->hasIndex('students', 'students_email_unique')) {
                $table->unique('email');
            }
            if (! $this->hasIndex('students', 'students_admission_id_unique')) {
                $table->unique('admission_id');
            }
            if (! $this->hasIndex('students', 'students_user_id_unique')) {
                $table->unique('user_id');
            }
        });

        if (Schema::hasColumn('students', 'parent_user_id') && ! $this->hasIndex('students', 'students_parent_user_id_foreign')) {
            try {
                Schema::table('students', function (Blueprint $table) {
                    $table->foreign('parent_user_id')->references('id')->on('users')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // Index/FK may already exist under a different name.
            }
        }

        Schema::table('teachers', function (Blueprint $table) {
            if (! $this->hasIndex('teachers', 'teachers_user_id_unique')) {
                $table->unique('user_id');
            }
        });

        if (Schema::hasColumn('students', 'parent_user_id')) {
            $parents = DB::table('users')->where('role_name', 'Parent')->whereNotNull('email')->get(['id', 'email']);
            foreach ($parents as $parent) {
                DB::table('students')
                    ->whereNull('parent_user_id')
                    ->where('parent_email', $parent->email)
                    ->update(['parent_user_id' => $parent->id]);
            }
        }

        if (Schema::getConnection()->getDriverName() === 'mysql' && Schema::hasTable('attendances')) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','excused') NOT NULL");
        }

        if (Schema::hasTable('attendances')) {
            DB::table('attendances')
                ->where('status', 'present')
                ->where('remarks', 'like', 'Late%')
                ->update(['status' => 'late']);
            DB::table('attendances')
                ->where('status', 'absent')
                ->where('remarks', 'like', 'Excused%')
                ->update(['status' => 'excused']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('students', 'parent_user_id')) {
            Schema::table('students', function (Blueprint $table) {
                try {
                    $table->dropForeign(['parent_user_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('parent_user_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['user_id']);
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropUnique(['admission_id']);
            $table->dropUnique(['user_id']);
        });
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql' && Schema::hasTable('attendances')) {
            DB::table('attendances')->where('status', 'late')->update(['status' => 'present']);
            DB::table('attendances')->where('status', 'excused')->update(['status' => 'absent']);
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent') NOT NULL");
        }
    }

    private function nullEmptyStrings(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }
            DB::table($table)->where($column, '')->update([$column => null]);
        }
    }

    private function dedupeColumn(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $duplicates = DB::table($table)
            ->select($column, DB::raw('COUNT(*) as aggregate'))
            ->whereNotNull($column)
            ->groupBy($column)
            ->having('aggregate', '>', 1)
            ->pluck($column);

        foreach ($duplicates as $value) {
            $ids = DB::table($table)->where($column, $value)->orderBy('id')->pluck('id');
            $ids->shift();
            foreach ($ids as $id) {
                DB::table($table)->where('id', $id)->update([
                    $column => $value.'-dup'.$id,
                ]);
            }
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

            return count($rows) > 0;
        }

        $rows = DB::select("PRAGMA index_list('{$table}')");
        foreach ($rows as $row) {
            if (($row->name ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }
};
