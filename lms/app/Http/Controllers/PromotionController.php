<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPromotion;
use App\Models\AcademicYear;
use App\Models\StudentGpa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Display promotion page
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get students grouped by grade level
        $gradeLevels = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 
                       'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        
        $studentsByGrade = [];
        foreach ($gradeLevels as $grade) {
            $count = Student::where('year_level', $grade)
                ->where('enrollment_status', 'active')
                ->count();
            if ($count > 0) {
                $studentsByGrade[$grade] = $count;
            }
        }
        
        return view('promotions.index', compact('academicYears', 'studentsByGrade', 'gradeLevels'));
    }

    /**
     * Show promotion form for specific grade
     */
    public function create(Request $request)
    {
        $fromGradeLevel = $request->get('from_grade');
        $toGradeLevel = $request->get('to_grade');
        
        if (!$fromGradeLevel || !$toGradeLevel) {
            Toastr::error('Please select grade levels', 'Error');
            return redirect()->route('promotions.index');
        }
        
        // Get students from the selected grade level
        $students = Student::where('year_level', $fromGradeLevel)
            ->where('enrollment_status', 'active')
            ->with(['user', 'gpaRecords' => function($query) {
                $query->latest();
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        return view('promotions.create', compact('students', 'fromGradeLevel', 'toGradeLevel', 'academicYears'));
    }

    /**
     * Process bulk promotion
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_grade' => 'required|string',
            'to_grade' => 'required|string',
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id',
            'promotion_date' => 'required|date',
            'students' => 'required|array|min:1',
            'students.*' => 'exists:students,id',
            'promotion_status' => 'array',
            'remarks' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $promoted = 0;
            $retained = 0;
            $graduated = 0;

            foreach ($request->students as $studentId) {
                $status = $request->promotion_status[$studentId] ?? 'promoted';
                $remarks = $request->remarks[$studentId] ?? null;
                
                // Get student's latest GPA
                $latestGpa = StudentGpa::where('student_id', $studentId)
                    ->where('academic_year_id', $request->from_academic_year_id)
                    ->latest()
                    ->first();
                
                // Create promotion record
                StudentPromotion::create([
                    'student_id' => $studentId,
                    'promoted_by' => auth()->id(),
                    'from_year_level' => $request->from_grade,
                    'to_year_level' => $status === 'retained' ? $request->from_grade : $request->to_grade,
                    'from_academic_year_id' => $request->from_academic_year_id,
                    'to_academic_year_id' => $request->to_academic_year_id,
                    'promotion_status' => $status,
                    'remarks' => $remarks,
                    'final_gpa' => $latestGpa ? $latestGpa->gpa : null,
                    'promotion_date' => $request->promotion_date,
                ]);
                
                // Update student's grade level
                $student = Student::find($studentId);
                if ($status === 'promoted') {
                    $student->update(['year_level' => $request->to_grade]);
                    $promoted++;
                } elseif ($status === 'retained') {
                    // Keep same grade level
                    $retained++;
                } elseif ($status === 'graduated') {
                    $student->update([
                        'year_level' => $request->to_grade,
                        'enrollment_status' => 'graduated'
                    ]);
                    $graduated++;
                }
            }

            DB::commit();
            
            $message = "Promotion completed! Promoted: {$promoted}, Retained: {$retained}, Graduated: {$graduated}";
            Toastr::success($message, 'Success');
            
            return redirect()->route('promotions.history');
            
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Promotion failed: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show promotion history
     */
    public function history(Request $request)
    {
        $query = StudentPromotion::with(['student', 'promoter', 'fromAcademicYear', 'toAcademicYear'])
            ->orderBy('promotion_date', 'desc');
        
        // Filters
        if ($request->filled('academic_year_id')) {
            $query->where('from_academic_year_id', $request->academic_year_id);
        }
        
        if ($request->filled('from_grade')) {
            $query->where('from_year_level', $request->from_grade);
        }
        
        if ($request->filled('promotion_status')) {
            $query->where('promotion_status', $request->promotion_status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        
        $promotions = $query->paginate(20);
        
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $gradeLevels = ['Nursery', 'Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 
                       'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        
        // Statistics
        $stats = [
            'total' => StudentPromotion::count(),
            'promoted' => StudentPromotion::where('promotion_status', 'promoted')->count(),
            'retained' => StudentPromotion::where('promotion_status', 'retained')->count(),
            'graduated' => StudentPromotion::where('promotion_status', 'graduated')->count(),
        ];
        
        return view('promotions.history', compact('promotions', 'academicYears', 'gradeLevels', 'stats'));
    }

    /**
     * Show individual student's promotion history
     */
    public function studentHistory($studentId)
    {
        $student = Student::with(['promotions' => function($query) {
            $query->orderBy('promotion_date', 'desc');
        }])->findOrFail($studentId);
        
        return view('promotions.student-history', compact('student'));
    }

    /**
     * Delete a promotion record (rollback)
     */
    public function destroy($id)
    {
        try {
            $promotion = StudentPromotion::findOrFail($id);
            
            // Rollback the student's grade level
            $student = $promotion->student;
            if ($promotion->promotion_status === 'promoted' || $promotion->promotion_status === 'graduated') {
                $student->update(['year_level' => $promotion->from_year_level]);
                if ($promotion->promotion_status === 'graduated') {
                    $student->update(['enrollment_status' => 'active']);
                }
            }
            
            $promotion->delete();
            
            Toastr::success('Promotion record deleted and student rolled back', 'Success');
            return redirect()->back();
            
        } catch (\Exception $e) {
            Toastr::error('Failed to delete promotion: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }
}
