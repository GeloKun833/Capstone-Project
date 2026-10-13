<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'account_academic_year_id')) {
                $table->unsignedBigInteger('account_academic_year_id')->nullable()->after('role_name');
                $table->foreign('account_academic_year_id')
                    ->references('id')
                    ->on('academic_years')
                    ->nullOnDelete();
            }
        });

        $this->stampParentsFromApplications();
        $this->stampParentsFromSections();
        $this->stampStudentsFromApplications();
        $this->stampStudentsFromSections();
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'account_academic_year_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_academic_year_id']);
            $table->dropColumn('account_academic_year_id');
        });
    }

    private function stampParentsFromApplications(): void
    {
        if (! Schema::hasTable('enrollment_applications')) {
            return;
        }

        $rows = DB::table('enrollment_applications')
            ->whereNotNull('parent_user_id')
            ->whereNotNull('academic_year_id')
            ->orderBy('id')
            ->get(['parent_user_id', 'academic_year_id']);

        $firstYear = [];
        foreach ($rows as $row) {
            $parentId = (int) $row->parent_user_id;
            if (! isset($firstYear[$parentId])) {
                $firstYear[$parentId] = (int) $row->academic_year_id;
            }
        }

        foreach ($firstYear as $parentId => $yearId) {
            DB::table('users')
                ->where('id', $parentId)
                ->where('role_name', 'Parent')
                ->whereNull('account_academic_year_id')
                ->update(['account_academic_year_id' => $yearId]);
        }
    }

    private function stampParentsFromSections(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('student_section_assignments')) {
            return;
        }

        $rows = DB::table('students')
            ->join('student_section_assignments', 'student_section_assignments.student_id', '=', 'students.id')
            ->whereNotNull('students.parent_user_id')
            ->whereNotNull('student_section_assignments.academic_year_id')
            ->orderBy('student_section_assignments.id')
            ->get(['students.parent_user_id', 'student_section_assignments.academic_year_id']);

        $firstYear = [];
        foreach ($rows as $row) {
            $parentId = (int) $row->parent_user_id;
            if (! isset($firstYear[$parentId])) {
                $firstYear[$parentId] = (int) $row->academic_year_id;
            }
        }

        foreach ($firstYear as $parentId => $yearId) {
            DB::table('users')
                ->where('id', $parentId)
                ->where('role_name', 'Parent')
                ->whereNull('account_academic_year_id')
                ->update(['account_academic_year_id' => $yearId]);
        }
    }

    private function stampStudentsFromApplications(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('enrollment_applications')) {
            return;
        }

        $rows = DB::table('students')
            ->join('enrollment_applications', 'enrollment_applications.id', '=', 'students.enrollment_application_id')
            ->join('users', 'users.user_id', '=', 'students.user_id')
            ->where('users.role_name', 'Student')
            ->whereNull('users.account_academic_year_id')
            ->whereNotNull('enrollment_applications.academic_year_id')
            ->get(['users.id as user_id', 'enrollment_applications.academic_year_id']);

        foreach ($rows as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->whereNull('account_academic_year_id')
                ->update(['account_academic_year_id' => (int) $row->academic_year_id]);
        }
    }

    private function stampStudentsFromSections(): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('student_section_assignments')) {
            return;
        }

        $rows = DB::table('students')
            ->join('users', 'users.user_id', '=', 'students.user_id')
            ->join('student_section_assignments', 'student_section_assignments.student_id', '=', 'students.id')
            ->where('users.role_name', 'Student')
            ->whereNull('users.account_academic_year_id')
            ->whereNotNull('student_section_assignments.academic_year_id')
            ->orderBy('student_section_assignments.id')
            ->get(['users.id as user_id', 'student_section_assignments.academic_year_id']);

        $firstYear = [];
        foreach ($rows as $row) {
            $userId = (int) $row->user_id;
            if (! isset($firstYear[$userId])) {
                $firstYear[$userId] = (int) $row->academic_year_id;
            }
        }

        foreach ($firstYear as $userId => $yearId) {
            DB::table('users')
                ->where('id', $userId)
                ->whereNull('account_academic_year_id')
                ->update(['account_academic_year_id' => $yearId]);
        }
    }
};
