<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentSisService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class StudentController extends Controller
{
    /** index page student list */
    public function student()
    {
        $query = Student::query();
        if (request()->has('archived') && request('archived') == 1) {
            $query = $query->onlyTrashed();
            $showingArchived = true;
        } else {
            $showingArchived = false;
        }
        // Search/Filter logic
        if ($id = request('search_id')) {
            $query->where('id', 'like', "%$id%");
        }
        if ($name = request('search_name')) {
            $query->where(function($q) use ($name) {
                $q->where('first_name', 'like', "%$name%")
                  ->orWhere('last_name', 'like', "%$name%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$name%"]);
            });
        }
        if ($phone = request('search_phone')) {
            $query->where('phone_number', 'like', "%$phone%");
        }
        if ($class = request('search_class')) {
            $query->where('class', 'like', "%$class%");
        }
        if ($year = request('search_year_level')) {
            $query->where('year_level', 'like', "%$year%");
        }
        $studentList = $query->latest('id')->paginate(20)->withQueryString();
        return view('student.student',compact('studentList', 'showingArchived'));
    }

    /** index page student grid */
    public function studentGrid()
    {
        $query = Student::query();
        if (request()->has('archived') && request('archived') == 1) {
            $query = $query->onlyTrashed();
            $showingArchived = true;
        } else {
            $showingArchived = false;
        }
        // Search/Filter logic
        if ($id = request('search_id')) {
            $query->where('id', 'like', "%$id%");
        }
        if ($name = request('search_name')) {
            $query->where(function($q) use ($name) {
                $q->where('first_name', 'like', "%$name%")
                  ->orWhere('last_name', 'like', "%$name%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$name%"]);
            });
        }
        if ($phone = request('search_phone')) {
            $query->where('phone_number', 'like', "%$phone%");
        }
        if ($class = request('search_class')) {
            $query->where('class', 'like', "%$class%");
        }
        if ($year = request('search_year_level')) {
            $query->where('year_level', 'like', "%$year%");
        }
        $studentList = $query->latest('id')->paginate(20)->withQueryString();
        return view('student.student-grid',compact('studentList', 'showingArchived'));
    }

    /** student add page */
    public function studentAdd()
    {
        return view('student.add-student');
    }
    
    /** student save record */
    public function studentSave(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'gender'        => 'required|not_in:0',
            'date_of_birth' => 'required|string',
            'roll'          => 'required|string',
            'blood_group'   => 'required|string',
            'religion'      => 'required|string',
            'email'         => 'required|email',
            'class'         => 'required|string',
            'section'       => 'required|string',
            'admission_id'  => 'required|string',
            'phone_number'  => 'required',
            'upload'        => 'required|image',
        ]);
        
        DB::beginTransaction();
        try {
           
            $upload_file = rand() . '.' . $request->upload->extension();
            $request->upload->move(storage_path('app/public/student-photos/'), $upload_file);
            if(!empty($request->upload)) {
                $student = new Student;
                $student->first_name   = $request->first_name;
                $student->last_name    = $request->last_name;
                $student->gender       = $request->gender;
                $student->date_of_birth= $request->date_of_birth;
                $student->roll         = $request->roll;
                $student->blood_group  = $request->blood_group;
                $student->religion     = $request->religion;
                $student->email        = $request->email;
                $student->class        = $request->class;
                $student->section      = $request->section;
                $student->admission_id = $request->admission_id;
                $student->phone_number = $request->phone_number;
                $student->upload = $upload_file;
                $student->save();

                Toastr::success('Has been add successfully :)','Success');
                DB::commit();
            }

            return redirect()->back();
           
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('fail, Add new student  :)','Error');
            return redirect()->back();
        }
    }

    /** view for edit student */
    public function studentEdit($id)
    {
        $studentEdit = Student::where('id',$id)->first();
        return view('student.edit-student',compact('studentEdit'));
    }

    /** update record */
    public function studentUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id' => 'required|exists:students,id',
                'first_name' => \App\Support\FormRules::NAME,
                'last_name' => \App\Support\FormRules::NAME,
                'middle_name' => \App\Support\FormRules::NAME_OPTIONAL,
                'date_of_birth' => \App\Support\FormRules::DOB,
                'phone_number' => \App\Support\FormRules::PHONE,
                'upload' => \App\Support\FormRules::AVATAR,
            ], \App\Support\FormRules::messages());

            $student = Student::findOrFail($request->id);
            $upload_file = $student->upload;

            if ($request->hasFile('upload') && $request->file('upload')->isValid()) {
                $upload_file = rand().'.'.$request->upload->extension();
                $dest = storage_path('app/public/student-photos');
                if (! is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
                $request->upload->move($dest, $upload_file);
                if ($request->image_hidden && is_file($dest.'/'.$request->image_hidden)) {
                    @unlink($dest.'/'.$request->image_hidden);
                }
            }

            $student->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'date_of_birth' => $request->date_of_birth,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'address' => $request->address,
                'upload' => $upload_file,
            ]);

            // Sync linked user name (students cannot change role to Admin here)
            if ($student->user_id) {
                User::where('user_id', $student->user_id)->update([
                    'name' => trim($request->first_name.' '.$request->last_name),
                    'phone_number' => $request->phone_number,
                    'date_of_birth' => $request->date_of_birth,
                ]);
            }

            Toastr::success('Student updated successfully.', 'Success');
            DB::commit();
            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Failed to update student: '.$e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** student delete */
    public function studentDelete(Request $request)
    {
        DB::beginTransaction();
        try {
           
            if (!empty($request->id)) {
                Student::destroy($request->id);
                unlink(storage_path('app/public/student-photos/'.$request->avatar));
                DB::commit();
                Toastr::success('Student deleted successfully :)','Success');
                return redirect()->back();
            }
    
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Student deleted fail :)','Error');
            return redirect()->back();
        }
    }

    /** Restore archived student */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        \Brian2694\Toastr\Facades\Toastr::success('Student restored successfully :)','Success');
        return redirect()->back();
    }

    /** student profile page */
    public function studentProfile($id)
    {
        $student = Student::findOrFail($id);
        return view('student.student-profile', compact('student'));
    }

    /** student my classes page */
    public function myClasses()
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }
        
        // Get student's enrollments with related data
        $enrollments = $student->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('status', 'active')
            ->get();
        
        // Calculate comprehensive statistics
        $totalGrades = $student->grades()->count();
        $averageGrade = $student->grades()->avg('percentage') ?? 0;
        $totalAttendance = $student->attendanceRecords()->count();
        $presentAttendance = $student->attendanceRecords()->where('status', 'present')->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0;
        
        // Get real assignment data for each enrollment
        foreach ($enrollments as $enrollment) {
            $enrollment->assignment_count = \App\Models\Assignment::where('subject_id', $enrollment->subject_id)
                ->where('status', 'published')
                ->where('is_active', true)
                ->count();
            
            $enrollment->pending_assignments = \App\Models\Assignment::where('subject_id', $enrollment->subject_id)
                ->where('status', 'published')
                ->where('is_active', true)
                ->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->count();
            
            $enrollment->overdue_assignments = \App\Models\Assignment::where('subject_id', $enrollment->subject_id)
                ->where('status', 'published')
                ->where('is_active', true)
                ->where('due_date', '<', now())
                ->whereDoesntHave('submissions', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })
                ->count();
        }
        
        // Get upcoming deadlines from real assignments
        $upcomingAssignments = \App\Models\Assignment::whereIn('subject_id', $enrollments->pluck('subject_id'))
            ->where('status', 'published')
            ->where('is_active', true)
            ->where('due_date', '>', now())
            ->whereDoesntHave('submissions', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with(['subject'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();
        
        $upcomingDeadlines = $upcomingAssignments->map(function($assignment) {
            return [
                'title' => $assignment->title,
                'type' => 'assignment',
                'due_date' => $assignment->due_date,
                'subject' => $assignment->subject->subject_name,
                'icon' => 'fas fa-file-alt',
                'is_overdue' => false
            ];
        });
        
        // Calculate performance overview
        $performanceStats = [
            'average_grade' => round($averageGrade, 1),
            'attendance_rate' => $attendanceRate,
            'total_assignments' => 15, // Placeholder - can be enhanced with real data
            'total_quizzes' => 8, // Placeholder - can be enhanced with real data
            'total_grades' => $totalGrades,
            'total_attendance' => $totalAttendance
        ];
        
        return view('student.my-classes', compact(
            'student', 
            'enrollments', 
            'upcomingDeadlines', 
            'performanceStats'
        ));
    }

    /** student class detail page */
    public function classDetail($enrollmentId)
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }
        
        // Get the specific enrollment with all related data
        $enrollment = $student->enrollments()
            ->with(['subject', 'academicYear', 'semester'])
            ->where('id', $enrollmentId)
            ->where('status', 'active')
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'Class not found or access denied.');
        }

        // Display identifiers (never use fake fallbacks like "4IT-B")
        $studentNumber = $student->admission_id
            ?: ($student->roll ?: ('STU' . $student->id));

        $sectionQuery = $student->sections()->orderBy('sections.name');
        if ($enrollment->academic_year_id) {
            $sectionQuery->wherePivot('academic_year_id', $enrollment->academic_year_id);
        }
        if ($enrollment->semester_id) {
            $sectionQuery->wherePivot('semester_id', $enrollment->semester_id);
        }
        $classSection = $sectionQuery->first()
            ?: $student->sections()->orderBy('sections.name')->first();

        // Prefer assigned section name; otherwise subject grade/class label
        $sectionLabel = $classSection
            ? trim($classSection->name . ($classSection->grade_level ? ' (' . $classSection->grade_level . ')' : ''))
            : ($enrollment->subject->class ?? null);

        // Get assignments for this specific subject (published)
        $assignmentsQuery = \App\Models\Assignment::with(['teacher', 'subject', 'section'])
            ->where('subject_id', $enrollment->subject_id)
            ->where('status', 'published')
            ->where('is_active', true);

        // Get online classes / lessons for this subject
        $onlineClassesQuery = \App\Models\Lesson::with(['teacher', 'subject', 'section', 'activities'])
            ->where('subject_id', $enrollment->subject_id)
            ->where('is_active', true)
            ->whereIn('status', ['published', 'completed']);

        // Prefer lessons/assignments for the student's assigned section(s),
        // but still show subject-level items so published lessons are visible.
        $studentSectionIds = $student->sections()->pluck('sections.id')->filter()->unique()->values();
        if ($studentSectionIds->isNotEmpty()) {
            $assignmentsQuery->where(function ($q) use ($studentSectionIds) {
                $q->whereIn('section_id', $studentSectionIds)
                    ->orWhereNull('section_id');
            });
            $onlineClassesQuery->where(function ($q) use ($studentSectionIds) {
                $q->whereIn('section_id', $studentSectionIds)
                    ->orWhereNull('section_id');
            });
        }

        $assignments = $assignmentsQuery->orderBy('due_date', 'asc')->get();

        // If section filter hid everything but subject lessons exist, fall back to subject lessons
        $onlineClasses = $onlineClassesQuery->orderBy('lesson_date', 'desc')->get();
        if ($onlineClasses->isEmpty() && $studentSectionIds->isNotEmpty()) {
            $onlineClasses = \App\Models\Lesson::with(['teacher', 'subject', 'section', 'activities'])
                ->where('subject_id', $enrollment->subject_id)
                ->where('is_active', true)
                ->whereIn('status', ['published', 'completed'])
                ->orderBy('lesson_date', 'desc')
                ->get();
        }

        // Get quizzes/exams (using Activity model) for this subject's lessons
        $lessonIds = $onlineClasses->pluck('id');
        $quizzes = collect();
        if ($lessonIds->isNotEmpty()) {
            $quizzes = \App\Models\Activity::with(['lesson.teacher', 'lesson.subject', 'lesson.section'])
                ->whereIn('lesson_id', $lessonIds)
                ->where('is_active', true)
                ->orderBy('due_date', 'asc')
                ->get();
        }

        // Debug logging
        Log::info('Student class detail - Lessons query', [
            'student_id' => $student->id,
            'enrollment_id' => $enrollmentId,
            'subject_id' => $enrollment->subject_id,
            'student_section_ids' => $studentSectionIds->all(),
            'lessons_found' => $onlineClasses->count(),
        ]);

        // Get class posts for this subject
        $classPosts = \App\Models\ClassPost::with(['teacher', 'subject', 'section'])
            ->where('subject_id', $enrollment->subject_id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get grades for this subject
        $grades = $student->grades()
            ->where('subject_id', $enrollment->subject_id)
            ->with(['teacher', 'component', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get the active tab from request
        $activeTab = request('tab', 'lessons');

        return view('student.class-detail', compact(
            'student',
            'enrollment',
            'activeTab',
            'assignments',
            'quizzes',
            'onlineClasses',
            'classPosts',
            'grades',
            'studentNumber',
            'sectionLabel',
            'classSection'
        ));
    }

    /**
     * Student view of a published lesson for an enrolled class.
     */
    public function lessonShow($enrollmentId, $lessonId)
    {
        $user = auth()->user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $enrollment = $student->enrollments()
            ->with(['subject'])
            ->where('id', $enrollmentId)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Class not found or access denied.');
        }

        $lesson = \App\Models\Lesson::with(['teacher', 'subject', 'section', 'academicYear', 'semester', 'activities'])
            ->where('id', $lessonId)
            ->where('subject_id', $enrollment->subject_id)
            ->where('is_active', true)
            ->whereIn('status', ['published', 'completed'])
            ->first();

        if (!$lesson) {
            return redirect()
                ->route('student.class.detail', ['enrollmentId' => $enrollmentId, 'tab' => 'lessons'])
                ->with('error', 'Lesson not found or not available for this class.');
        }

        $activityIds = $lesson->activities->pluck('id');
        $mySubmissions = collect();
        if ($activityIds->isNotEmpty()) {
            $mySubmissions = \App\Models\ActivitySubmission::query()
                ->where('student_id', $student->id)
                ->whereIn('activity_id', $activityIds)
                ->get()
                ->keyBy('activity_id');
        }

        return view('student.lesson-show', compact('student', 'enrollment', 'lesson', 'mySubmissions'));
    }

    /** student grades page */
    public function grades(Request $request)
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }
        
        // Get selected academic year from request
        $selectedAcademicYearId = $request->get('academic_year_id');
        
        // Get all academic years
        $academicYears = \App\Models\AcademicYear::orderBy('name', 'desc')->get();
        $currentAcademicYear = $selectedAcademicYearId 
            ? \App\Models\AcademicYear::find($selectedAcademicYearId) 
            : $academicYears->first();
        
        // Get all subjects the student is enrolled in or assigned to
        $allSubjects = collect();
        $quarterlyGrades = collect();

        if ($currentAcademicYear) {
            // Get subjects from enrollments
            $enrolledSubjects = $student->enrollments()
                ->where('academic_year_id', $currentAcademicYear->id)
                ->with('subject')
                ->get()
                ->pluck('subject')
                ->filter()
                ->unique('id');
            
            // Get subjects from section assignments (via class schedules)
            $studentSections = $student->sections()
                ->wherePivot('academic_year_id', $currentAcademicYear->id)
                ->pluck('sections.id');
            
            $sectionSubjects = \App\Models\Subject::whereHas('classSchedules', function($query) use ($studentSections) {
                $query->whereIn('section_id', $studentSections)
                      ->where('is_active', true);
            })->get();
            
            // Merge all subjects and remove duplicates
            $allSubjects = $enrolledSubjects->merge($sectionSubjects)->unique('id')->sortBy('subject_name');
            
            // Get existing quarterly grades
            $existingGrades = \App\Models\QuarterlyGrade::where('student_id', $student->id)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->with(['subject', 'teacher', 'academicYear'])
                ->get()
                ->keyBy('subject_id');

            // Include subjects that already have quarterly grades entered by teachers
            $gradedSubjects = \App\Models\Subject::whereIn('id', $existingGrades->keys())->get();
            $allSubjects = $allSubjects->merge($gradedSubjects)->unique('id')->sortBy('subject_name');
            
            // Create a collection with all subjects and their grades (if any)
            $quarterlyGrades = $allSubjects->map(function($subject) use ($existingGrades, $student, $currentAcademicYear) {
                $grade = $existingGrades->get($subject->id);
                
                if ($grade) {
                    // Return existing grade record
                    return $grade;
                } else {
                    // Create a placeholder grade record for display
                    $placeholder = new \App\Models\QuarterlyGrade();
                    $placeholder->id = null;
                    $placeholder->student_id = $student->id;
                    $placeholder->subject_id = $subject->id;
                    $placeholder->academic_year_id = $currentAcademicYear->id;
                    $placeholder->quarter_1 = null;
                    $placeholder->quarter_2 = null;
                    $placeholder->quarter_3 = null;
                    $placeholder->quarter_4 = null;
                    $placeholder->final_grade = null;
                    $placeholder->remarks = null;
                    $placeholder->setRelation('subject', $subject);
                    $placeholder->setRelation('academicYear', $currentAcademicYear);
                    
                    return $placeholder;
                }
            })->sortBy(function($grade) {
                return $grade->subject->subject_name ?? '';
            })->values();
        }

        // General averages (DepEd report card footer)
        $generalAverages = [
            'q1' => null,
            'q2' => null,
            'q3' => null,
            'q4' => null,
            'final' => null,
        ];
        if ($quarterlyGrades->isNotEmpty()) {
            foreach (['q1' => 'quarter_1', 'q2' => 'quarter_2', 'q3' => 'quarter_3', 'q4' => 'quarter_4', 'final' => 'final_grade'] as $key => $field) {
                $values = $quarterlyGrades->pluck($field)->filter(fn ($v) => $v !== null && $v !== '')->map(fn ($v) => (float) $v);
                $generalAverages[$key] = $values->isNotEmpty() ? round($values->avg(), 2) : null;
            }
        }

        $observedIndicators = \App\Models\ObservedValueIndicator::active()->get()->groupBy('core_value');
        $observedRatings = collect();
        if ($currentAcademicYear) {
            $observedRatings = \App\Models\StudentObservedValue::where('student_id', $student->id)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->get()
                ->keyBy('indicator_id');
        }
        
        // Get GPA records
        $gpaRecords = $student->gpaRecords()
            ->with(['academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get grade alerts
        $gradeAlerts = $student->gradeAlerts()
            ->with(['subject'])
            ->where('is_resolved', false)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('student.grades', compact(
            'student', 
            'quarterlyGrades', 
            'gpaRecords', 
            'gradeAlerts',
            'academicYears',
            'currentAcademicYear',
            'generalAverages',
            'observedIndicators',
            'observedRatings'
        ));
    }

    /** student attendance page */
    public function attendance()
    {
        $user = auth()->user();
        $student = $user->student;
        
        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }
        
        // Get student's subjects
        $subjects = $student->subjects;
        
        // Get attendance records for current month
        $month = request('month', now()->format('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        
        $query = \App\Models\Attendance::where('student_id', $student->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->with(['subject', 'teacher']);
        
        if ($subjectId = request('subject_id')) {
            $query->where('subject_id', $subjectId);
        }
        
        $attendances = $query->orderBy('date', 'desc')->get();
        
        // Calculate summary
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $total - $present;
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        $summary = [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'percentage' => $percentage,
        ];
        
        return view('student.attendance', compact('student', 'subjects', 'attendances', 'summary'));
    }

    /**
     * View Student Information System (SIS) - Comprehensive student profile
     */
    public function viewSIS($user_id, StudentSisService $sisService)
    {
        $data = $sisService->buildProfile($user_id);

        return view('student.sis', $data);
    }
}
