<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addYearColumn('subject_teacher');
        $this->addYearColumn('section_teacher');
        $this->addYearColumn('teacher_grade_level');

        $historicalId = DB::table('academic_years')
            ->whereIn('name', ['2025-2026', '2025–2026'])
            ->value('id');

        if ($historicalId) {
            foreach (['subject_teacher', 'section_teacher', 'teacher_grade_level'] as $table) {
                DB::table($table)->whereNull('academic_year_id')->update([
                    'academic_year_id' => $historicalId,
                ]);
            }
        }

        $this->replaceUnique('subject_teacher', ['subject_id', 'teacher_id']);
        $this->replaceUnique('section_teacher', ['teacher_id', 'section_id']);
        $this->replaceUnique('teacher_grade_level', ['teacher_id', 'grade_level']);
    }

    public function down(): void
    {
        foreach ([
            'subject_teacher' => ['subject_id', 'teacher_id'],
            'section_teacher' => ['teacher_id', 'section_id'],
            'teacher_grade_level' => ['teacher_id', 'grade_level'],
        ] as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                $blueprint->dropUnique($table.'_year_unique');
                $blueprint->dropConstrainedForeignId('academic_year_id');
                $blueprint->unique($columns);
            });
        }
    }

    private function addYearColumn(string $table): void
    {
        if (Schema::hasColumn($table, 'academic_year_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreignId('academic_year_id')
                ->nullable()
                ->after('id')
                ->constrained('academic_years')
                ->nullOnDelete();
        });
    }

    private function replaceUnique(string $table, array $columns): void
    {
        $yearIndex = $table.'_year_unique';
        $legacyIndex = $table.'_'.implode('_', $columns).'_unique';

        if (! $this->indexExists($table, $yearIndex)) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $yearIndex) {
                $blueprint->unique(array_merge($columns, ['academic_year_id']), $yearIndex);
            });
        }

        if ($this->indexExists($table, $legacyIndex)) {
            Schema::table($table, function (Blueprint $blueprint) use ($legacyIndex) {
                $blueprint->dropUnique($legacyIndex);
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]);

        return $rows !== [];
    }
};
