<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\StudentGpa;
use Illuminate\Support\Facades\DB;

class DeleteAllStudents extends Command
{
    protected $signature = 'delete:all-students';
    protected $description = 'Delete all students and related data from the system';

    public function handle()
    {
        $this->info("=== Checking for Students ===");
        
        // Get all students
        $students = Student::withTrashed()->get();
        
        if ($students->count() === 0) {
            $this->warn("No students found in the system!");
            return;
        }
        
        $this->info("Found {$students->count()} student(s):");
        $this->newLine();
        
        foreach ($students as $student) {
            $this->info("ID: {$student->id} - {$student->first_name} {$student->last_name} ({$student->email}) - Grade: {$student->year_level}");
        }
        
        $this->newLine();
        $this->warn("This will PERMANENTLY delete all students and their:");
        $this->warn("  - User accounts");
        $this->warn("  - Enrollments");
        $this->warn("  - Grades");
        $this->warn("  - Attendance records");
        $this->warn("  - GPA records");
        $this->warn("This action CANNOT be undone!");
        $this->newLine();
        
        DB::beginTransaction();
        
        try {
            $deletedStudents = 0;
            $deletedUsers = 0;
            $deletedEnrollments = 0;
            $deletedGrades = 0;
            $deletedAttendance = 0;
            $deletedGPA = 0;
            
            foreach ($students as $student) {
                $this->info("Processing: {$student->first_name} {$student->last_name}...");
                
                // Delete enrollments
                $enrollmentCount = Enrollment::where('student_id', $student->id)->count();
                Enrollment::where('student_id', $student->id)->delete();
                $deletedEnrollments += $enrollmentCount;
                
                // Delete grades
                $gradeCount = Grade::where('student_id', $student->id)->count();
                Grade::where('student_id', $student->id)->delete();
                $deletedGrades += $gradeCount;
                
                // Delete attendance
                $attendanceCount = Attendance::where('student_id', $student->id)->count();
                Attendance::where('student_id', $student->id)->delete();
                $deletedAttendance += $attendanceCount;
                
                // Delete GPA records
                $gpaCount = StudentGpa::where('student_id', $student->id)->count();
                StudentGpa::where('student_id', $student->id)->delete();
                $deletedGPA += $gpaCount;
                
                // Find and delete related user
                $user = User::where('email', $student->email)->first();
                if ($user) {
                    $user->delete();
                    $deletedUsers++;
                    $this->info("  ✅ Deleted user account: {$user->name}");
                }
                
                // Delete student (force delete to bypass soft delete)
                $student->forceDelete();
                $deletedStudents++;
                $this->info("  ✅ Deleted student profile");
            }
            
            DB::commit();
            
            $this->newLine();
            $this->info("=== Deletion Summary ===");
            $this->info("✅ Students deleted: {$deletedStudents}");
            $this->info("✅ Users deleted: {$deletedUsers}");
            $this->info("✅ Enrollments deleted: {$deletedEnrollments}");
            $this->info("✅ Grades deleted: {$deletedGrades}");
            $this->info("✅ Attendance records deleted: {$deletedAttendance}");
            $this->info("✅ GPA records deleted: {$deletedGPA}");
            $this->info("All student data has been successfully deleted!");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}

