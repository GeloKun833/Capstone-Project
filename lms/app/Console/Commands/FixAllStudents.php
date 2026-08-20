<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\EnrollmentApplication;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;

class FixAllStudents extends Command
{
    protected $signature = 'fix:all-students';
    protected $description = 'Fix all approved students to ensure they have proper profiles and subject enrollments';

    public function handle()
    {
        $this->info("=== Fixing All Approved Students ===");
        
        // Get all approved enrollment applications
        $approvedApplications = EnrollmentApplication::where('status', 'approved')->get();
        
        if ($approvedApplications->count() === 0) {
            $this->error("No approved applications found!");
            return;
        }
        
        $this->info("Found {$approvedApplications->count()} approved applications");
        
        $fixedCount = 0;
        $errorCount = 0;
        
        foreach ($approvedApplications as $application) {
            $this->info("\n--- Processing: {$application->full_name} ({$application->email}) ---");
            
            try {
                // Check if user exists
                $user = User::where('email', $application->email)->first();
                if (!$user) {
                    $this->warn("⚠️  User not found for {$application->email}");
                    $errorCount++;
                    continue;
                }
                
                $this->info("✅ User found: {$user->name} (ID: {$user->user_id})");
                
                // Check if student profile exists and is properly linked
                $student = Student::where('user_id', $user->user_id)->first();
                
                if (!$student) {
                    $this->info("🔧 Creating student profile...");
                    
                    // Create student record
                    $student = Student::create([
                        'user_id' => $user->user_id,
                        'first_name' => $application->first_name,
                        'last_name' => $application->last_name,
                        'middle_name' => $application->middle_name,
                        'gender' => $application->gender,
                        'date_of_birth' => $application->date_of_birth,
                        'email' => $application->email,
                        'phone_number' => $application->phone_number,
                        'address' => $application->address,
                        'parent_email' => $application->parent_email,
                        'parent_name' => $application->parent_name,
                        'parent_phone' => $application->parent_phone,
                        'parent_relationship' => $application->parent_relationship,
                        'emergency_contact_name' => $application->emergency_contact_name,
                        'emergency_contact_phone' => $application->emergency_contact_phone,
                        'previous_school' => $application->previous_school,
                        'enrollment_application_id' => $application->id,
                        'enrollment_status' => 'active',
                        'year_level' => $application->grade_level_applying_for,
                    ]);
                    
                    $this->info("✅ Created student profile: {$student->first_name} {$student->last_name}");
                } else {
                    // Ensure student is properly linked
                    if (empty($student->user_id)) {
                        $this->info("🔧 Linking student to user...");
                        $student->update(['user_id' => $user->user_id]);
                    }
                    
                    $this->info("✅ Student profile exists: {$student->first_name} {$student->last_name}");
                }
                
                // Check enrollments
                $enrollments = Enrollment::where('student_id', $student->id)->count();
                $this->info("📚 Current enrollments: {$enrollments}");
                
                if ($enrollments === 0) {
                    $this->info("🔧 Creating subject enrollments...");
                    
                    // Get academic year and semester
                    $academicYear = AcademicYear::latest()->first();
                    $semester = Semester::latest()->first();
                    
                    if (!$academicYear || !$semester) {
                        $this->error("❌ No academic year or semester found!");
                        $errorCount++;
                        continue;
                    }
                    
                    // Get subjects for grade level
                    $gradeLevel = $application->grade_level_applying_for;
                    $gradeSubjects = config("grade_subjects.{$gradeLevel}", []);
                    
                    if (empty($gradeSubjects)) {
                        $this->error("❌ No subjects configured for grade level: {$gradeLevel}");
                        $errorCount++;
                        continue;
                    }
                    
                    $this->info("📖 Found subjects for {$gradeLevel}: " . implode(', ', $gradeSubjects));
                    
                    $enrolledCount = 0;
                    foreach ($gradeSubjects as $subjectName) {
                        // Find or create subject
                        $subject = Subject::where('subject_name', $subjectName)
                            ->where('class', $gradeLevel)
                            ->first();
                        
                        if (!$subject) {
                            $subject = Subject::create([
                                'subject_name' => $subjectName,
                                'class' => $gradeLevel,
                            ]);
                            $this->info("   Created subject: {$subjectName}");
                        }
                        
                        // Check if already enrolled
                        $existingEnrollment = Enrollment::where([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'academic_year_id' => $academicYear->id,
                            'semester_id' => $semester->id,
                        ])->first();
                        
                        if (!$existingEnrollment) {
                            Enrollment::create([
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'academic_year_id' => $academicYear->id,
                                'semester_id' => $semester->id,
                                'enrollment_date' => now(),
                                'status' => 'active',
                            ]);
                            $enrolledCount++;
                        }
                    }
                    
                    $this->info("✅ Created {$enrolledCount} new enrollments");
                } else {
                    $this->info("✅ Student already has {$enrollments} enrollments");
                }
                
                // Final verification
                $finalEnrollments = Enrollment::where('student_id', $student->id)->count();
                $this->info("✅ Total enrollments: {$finalEnrollments}");
                
                $fixedCount++;
                
            } catch (\Exception $e) {
                $this->error("❌ Error processing {$application->full_name}: " . $e->getMessage());
                $errorCount++;
            }
        }
        
        $this->info("\n=== Summary ===");
        $this->info("✅ Successfully fixed: {$fixedCount} students");
        if ($errorCount > 0) {
            $this->error("❌ Errors encountered: {$errorCount} students");
        }
        
        $this->info("All approved students should now have proper profiles and subject enrollments!");
    }
}

