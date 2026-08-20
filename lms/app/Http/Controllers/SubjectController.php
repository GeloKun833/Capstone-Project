<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Subject;

use Brian2694\Toastr\Facades\Toastr;

class SubjectController extends Controller
{
    /** index page */
    public function subjectList()
    {
        $query = Subject::query();
        
        // Search/Filter logic
        if ($id = request('search_id')) {
            $query->where('subject_id', 'like', "%$id%");
        }
        if ($name = request('search_name')) {
            $query->where('subject_name', 'like', "%$name%");
        }
        if ($class = request('search_class')) {
            $query->where('class', 'like', "%$class%");
        }
        
        $subjectList = $query->get();
        return view('subjects.subject_list',compact('subjectList'));
    }

    /** subject add */
    public function subjectAdd()
    {
        return view('subjects.subject_add');
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string',
            'class'        => 'required|string',
        ]);
        
        DB::beginTransaction();
        try {
                $saveRecord = new Subject;
                $saveRecord->subject_name   = $request->subject_name;
                $saveRecord->class          = $request->class;
                $saveRecord->save();

                Toastr::success('Has been add successfully :)','Success');
                DB::commit();
            return redirect()->back();
           
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
        return view('subjects.subject_edit',compact('subjectEdit'));
    }

    /** update record */
    public function updateRecord(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $updateRecord = [
                'subject_name' => $request->subject_name,
                'class'        => $request->class,
            ];

            Subject::where('subject_id',$request->subject_id)->update($updateRecord);
            Toastr::success('Has been update successfully :)','Success');
            DB::commit();
            return redirect()->back();
           
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

            Subject::where('subject_id',$request->subject_id)->delete();
            DB::commit();
            Toastr::success('Deleted record successfully :)','Success');
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Deleted record fail :)','Error');
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
