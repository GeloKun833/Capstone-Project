<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradingMenuSeeder extends Seeder
{
    public function run()
    {
        // First, let's check if Grading Management menu already exists
        $existingMenu = DB::table('menus')->where('title', 'Grading Management')->first();
        
        if ($existingMenu) {
            // Delete existing grading menu and its children
            DB::table('menus')->where('parent_id', $existingMenu->id)->delete();
            DB::table('menus')->where('id', $existingMenu->id)->delete();
        }

        // Insert parent menu "Grading Management"
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

        echo "Grading menu items added successfully!\n";
    }
} 