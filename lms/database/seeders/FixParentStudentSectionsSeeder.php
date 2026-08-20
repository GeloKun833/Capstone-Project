<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Section;

class FixParentStudentSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get students with parent_email but no sections
        $students = Student::whereNotNull('parent_email')->whereDoesntHave('sections')->get();
        $sections = Section::all();

        if ($students->isEmpty()) {
            echo "No students found that need section assignment.\n";
            return;
        }

        if ($sections->isEmpty()) {
            echo "No sections found. Cannot assign sections to students.\n";
            return;
        }

        foreach ($students as $student) {
            $section = $sections->random();
            $student->sections()->attach($section->id);
            echo "Assigned section '{$section->name}' to student '{$student->full_name}'\n";
        }

        echo "Section assignment completed successfully!\n";
    }
} 