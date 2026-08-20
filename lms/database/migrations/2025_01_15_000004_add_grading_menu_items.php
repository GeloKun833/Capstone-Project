<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert the "Grading Management" main menu
        $gradingMenuId = DB::table('menus')->insertGetId([
            'title' => 'Grading Management',
            'icon'  => 'fas fa-graduation-cap',
            'route' => null,
            'active_routes' => json_encode(['teacher.grading.grade-entry', 'teacher.grading.gpa-ranking', 'teacher.grading.performance-analytics']),
            'pattern'   => 'teacher/grading/*',
            'parent_id' => null,
            'order'     => 5, // After existing menus
            'is_active' => true,
        ]);

        // Insert submenu items under "Grading Management"
        DB::table('menus')->insert([
            [
                'title' => 'Grade Entry',
                'icon'  => 'fas fa-edit',
                'route' => 'teacher.grading.grade-entry',
                'active_routes' => json_encode(['teacher.grading.grade-entry']),
                'pattern'   => null,
                'parent_id' => $gradingMenuId,
                'order'     => 1,
                'is_active' => true,
            ],
            [
                'title' => 'GPA Ranking',
                'icon'  => 'fas fa-chart-bar',
                'route' => 'teacher.grading.gpa-ranking',
                'active_routes' => json_encode(['teacher.grading.gpa-ranking']),
                'pattern'   => null,
                'parent_id' => $gradingMenuId,
                'order'     => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Performance Analytics',
                'icon'  => 'fas fa-chart-line',
                'route' => 'teacher.grading.performance-analytics',
                'active_routes' => json_encode(['teacher.grading.performance-analytics']),
                'pattern'   => null,
                'parent_id' => $gradingMenuId,
                'order'     => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Weight Settings',
                'icon'  => 'fas fa-cog',
                'route' => 'teacher.grading.weight-settings',
                'active_routes' => json_encode(['teacher.grading.weight-settings']),
                'pattern'   => null,
                'parent_id' => $gradingMenuId,
                'order'     => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Grade Alerts',
                'icon'  => 'fas fa-exclamation-triangle',
                'route' => 'teacher.grading.grade-alerts',
                'active_routes' => json_encode(['teacher.grading.grade-alerts']),
                'pattern'   => null,
                'parent_id' => $gradingMenuId,
                'order'     => 5,
                'is_active' => true,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove grading menu items
        $gradingMenuId = DB::table('menus')
            ->where('title', 'Grading Management')
            ->value('id');

        if ($gradingMenuId) {
            // Remove submenu items
            DB::table('menus')->where('parent_id', $gradingMenuId)->delete();
            
            // Remove main menu
            DB::table('menus')->where('id', $gradingMenuId)->delete();
        }
    }
}; 