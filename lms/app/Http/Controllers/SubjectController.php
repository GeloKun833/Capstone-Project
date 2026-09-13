<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Subject;
use App\Services\GradeSubjectCatalogService;

use Brian2694\Toastr\Facades\Toastr;

class SubjectController extends Controller
{
    /** index page */
    public function subjectList()
    {
        $query = Subject::query();
        
        if ($id = request('search_id')) {
            $query->where('subject_id', 'like', "%$id%");
        }
        if ($name = request('search_name')) {
            $query->where('subject_name', 'like', "%$name%");
        }
        if ($class = request('search_class')) {
            $query->where('class', $class);
        }
        
        $subjectList = $query->orderBy('class')->orderBy('subject_name')->get();
        $subjectsByGrade = $subjectList->groupBy(fn ($s) => $s->class ?: 'Unassigned');
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();

        return view('subjects.subject_list', compact('subjectList', 'subjectsByGrade', 'gradeLevels'));
    }

    /** subject add */
    public function subjectAdd()
    {
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();
        return view('subjects.subject_add', compact('gradeLevels'));
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'class'        => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
        ]);

        $exists = Subject::where('subject_name', $request->subject_name)
            ->where('class', $request->class)
            ->exists();
        if ($exists) {
            Toastr::error('This subject already exists for that grade.', 'Error');
            return redirect()->back()->withInput();
        }
        
        DB::beginTransaction();
        try {
                $saveRecord = new Subject;
                $saveRecord->subject_name   = $request->subject_name;
                $saveRecord->class          = $request->class;
                $saveRecord->save();

                $enrolled = app(GradeSubjectCatalogService::class)
                    ->syncMissingEnrollmentsForGrade($request->class);
                $msg = 'Subject added for ' . $request->class . '.';
                if ($enrolled > 0) {
                    $msg .= " Enrolled {$enrolled} student class link(s) so it appears on My Classes.";
                } else {
                    $msg .= ' It will appear on the enrollment form for new students.';
                }
                Toastr::success($msg, 'Success');
                DB::commit();
            return redirect()->route('subject/list/page', ['search_class' => $request->class]);
           
        } catch(\Exception $e) {
            \Log::info($e);
            DB::rollback();
            Toastr::error('fail, Add new record:)','Error');
            return redirect()->back();
        }
    }

    /** subject edit view */
    public function subjectEdit($subject_id)
    {
        $subjectEdit = Subject::where('subject_id',$subject_id)->first();
        $gradeLevels = GradeSubjectCatalogService::gradeLevels();
        return view('subjects.subject_edit', compact('subjectEdit', 'gradeLevels'));
    }

    /** update record */
    public function updateRecord(Request $request)
    {
        $request->validate([
            'subject_id'   => 'required',
            'subject_name' => 'required|string|max:255',
            'class'        => 'required|string|in:' . implode(',', GradeSubjectCatalogService::gradeLevels()),
        ]);

        DB::beginTransaction();
        try {
            
            $updateRecord = [
                'subject_name' => $request->subject_name,
                'class'        => $request->class,
            ];

            Subject::where('subject_id',$request->subject_id)->update($updateRecord);
            Toastr::success('Subject updated. Enrollment form uses this catalog by grade.','Success');
            DB::commit();
            return redirect()->route('subject/list/page', ['search_class' => $request->class]);
           
        } catch(\Exception $e) {
            \Log::info($e);
            DB::rollback();
            Toastr::error('Fail, update record:)','Error');
            return redirect()->back();
        }
    }

    /** delete record */
    public function deleteRecord(Request $request)
    {
        DB::beginTransaction();
        try {
            $subject = Subject::where('subject_id', $request->subject_id)->first()
                ?? Subject::find($request->id);

            if (!$subject) {
                Toastr::error('Subject not found.', 'Error');
                return redirect()->back();
            }

            $subjectId = $subject->id;
            $grade = (string) ($subject->class ?? '');
            $studentIds = \App\Models\Enrollment::where('subject_id', $subjectId)->pluck('student_id')->unique()->filter();

            $subject->teachers()->detach();
            $subject->sections()->detach();
            if (method_exists($subject, 'curricula')) {
                $subject->curricula()->detach();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('class_schedules')) {
                DB::table('class_schedules')->where('subject_id', $subjectId)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('curriculum_subject')) {
                DB::table('curriculum_subject')->where('subject_id', $subjectId)->delete();
            }

            \App\Models\Enrollment::where('subject_id', $subjectId)->delete();

            foreach ($studentIds as $studentId) {
                \Illuminate\Support\Facades\Cache::forget('student.dashboard.v2.'.$studentId);
                $student = \App\Models\Student::with('user')->find($studentId);
                if ($student && $student->user) {
                    \App\Support\SidebarMenu::forgetForUser($student->user);
                }
            }

            if ($grade !== '') {
                \Illuminate\Support\Facades\Cache::forget('catalog.subjects.'.md5($grade));
            }

            $subject->delete();
            DB::commit();
            Toastr::success('Subject deleted. Student classes updated.','Success');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Subject delete failed: '.$e->getMessage());
            Toastr::error('Deleted record fail: '.$e->getMessage(),'Error');
            return redirect()->back();
        }
    }

    /** Show form to assign teachers to a subject */
    public function assignTeachersForm($id)
    {
        $subject = Subject::findOrFail($id);
        
        // Get all users with Teacher role
        $teacherUsers = \App\Models\User::where('role_name', 'Teacher')->get();
        
        // Create teacher records for users who don't have them
        foreach ($teacherUsers as $user) {
            if (!\App\Models\Teacher::where('user_id', $user->user_id)->exists()) {
                \App\Models\Teacher::create([
                    'user_id' => $user->user_id,
                    'full_name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'address' => '', // Default empty
                    'gender' => '', // Default empty
                    'date_of_birth' => null,
                    'qualification' => '', // Default empty
                    'experience' => '', // Default empty
                    'upload' => 'photo_defaults.jpg', // Default avatar
                ]);
            }
        }
        
        // Only get teachers who have valid user relationships with role "Teacher"
        $teachers = \App\Models\Teacher::with('user')
            ->whereHas('user', function($query) {
                $query->where('role_name', 'Teacher');
            })
            ->get();
            
        $assigned = $subject->teachers->pluck('id')->toArray();
        
        return view('subjects.assign_teachers', compact('subject', 'teachers', 'assigned'));
    }

    /** Handle assignment of teachers to a subject */
    public function assignTeachers(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $teacherIds = $request->input('teacher_ids', []);
        
        // Ensure all teacher IDs exist in the teachers table
        $validTeacherIds = \App\Models\Teacher::whereIn('id', $teacherIds)->pluck('id')->toArray();
        
        $subject->teachers()->sync($validTeacherIds);
        \Brian2694\Toastr\Facades\Toastr::success('Teachers assigned successfully :)', 'Success');
        return redirect()->route('subject/list/page');
    }
}
