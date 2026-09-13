<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ClassPost;
use App\Models\ClassSchedule;
use App\Models\Teacher;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;

class TeacherController extends Controller
{
    /** add teacher page */
    public function teacherAdd()
    {
        // Get all users with Teacher role (active status)
        // Prioritize teachers without complete details in teachers table
        $users = User::where('role_name', 'Teacher')
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();
        
        return view('teacher.add-teacher', compact('users'));
    }

    /** teacher list */
    public function teacherList()
    {
        $query = Teacher::with(['subjects', 'sections', 'user'])
                    ->join('users', 'teachers.user_id','users.user_id')
                    ->select('users.date_of_birth','users.join_date','users.phone_number as user_phone','users.avatar','users.name as user_name','teachers.*')
                    ->where('users.role_name', 'Teacher'); // Only show teachers with valid Teacher role
        
        // Search/Filter logic
        if ($id = request('search_id')) {
            $query->where('teachers.user_id', 'like', "%$id%");
        }
        if ($name = request('search_name')) {
            $query->where(function($q) use ($name) {
                $q->where('teachers.full_name', 'like', "%$name%")
                  ->orWhere('users.name', 'like', "%$name%");
            });
        }
        if ($phone = request('search_phone')) {
            $query->where(function($q) use ($phone) {
                $q->where('teachers.phone_number', 'like', "%$phone%")
                  ->orWhere('users.phone_number', 'like', "%$phone%");
            });
        }
        
        $listTeacher = $query->paginate(20)->withQueryString();
        return view('teacher.list-teachers',compact('listTeacher'));
    }

    /** teacher Grid */
    public function teacherGrid()
    {
        $teacherGrid = Teacher::with('user')
            ->whereHas('user', function($query) {
                $query->where('role_name', 'Teacher');
            })
            ->paginate(20)
            ->withQueryString();
        return view('teacher.teachers-grid',compact('teacherGrid'));
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|string|exists:users,user_id',
            'full_name'     => 'required|string',
            'gender'        => 'required|string',
            'experience'    => 'required|string',
            'date_of_birth' => \App\Support\FormRules::DOB_REQUIRED,
            'qualification' => 'required|string',
            'phone_number'  => 'required|string',
            'address'       => 'required|string',
            'city'          => 'required|string',
            'state'         => 'required|string',
            'zip_code'      => 'required|string',
            'country'       => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Check if teacher record already exists for this user_id
            $existingTeacher = Teacher::where('user_id', $request->user_id)->first();
            
            if ($existingTeacher) {
                // UPDATE existing record
                $existingTeacher->update([
                    'full_name'     => $request->full_name,
                    'gender'        => $request->gender,
                    'experience'    => $request->experience,
                    'qualification' => $request->qualification,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number'  => $request->phone_number,
                    'address'       => $request->address,
                    'city'          => $request->city,
                    'state'         => $request->state,
                    'zip_code'      => $request->zip_code,
                    'country'       => $request->country,
                ]);
                
                DB::commit();
                Toastr::success('Teacher details updated successfully!', 'Success');
            } else {
                // CREATE new record only if doesn't exist
                Teacher::create([
                    'user_id'       => $request->user_id,
                    'full_name'     => $request->full_name,
                    'gender'        => $request->gender,
                    'experience'    => $request->experience,
                    'qualification' => $request->qualification,
                    'date_of_birth' => $request->date_of_birth,
                    'phone_number'  => $request->phone_number,
                    'address'       => $request->address,
                    'city'          => $request->city,
                    'state'         => $request->state,
                    'zip_code'      => $request->zip_code,
                    'country'       => $request->country,
                ]);
                
                DB::commit();
                Toastr::success('Teacher details added successfully!', 'Success');
            }
            
            return redirect()->route('teacher/list/page');
            
        } catch(\Exception $e) {
            DB::rollback();
            Log::error('Teacher save error: ' . $e->getMessage());
            Toastr::error('Failed to save teacher details: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** edit record */
    public function editRecord($user_id)
    {
        $teacher = Teacher::join('users', 'teachers.user_id','users.user_id')
                    ->select('users.date_of_birth','users.join_date','users.phone_number','teachers.*')
                    ->where('users.user_id', $user_id)->first();
        return view('teacher.edit-teacher',compact('teacher'));
    }

    /** update record teacher */
    public function updateRecordTeacher(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id' => 'required|exists:teachers,id',
                'full_name' => \App\Support\FormRules::NAME,
                'phone_number' => \App\Support\FormRules::PHONE,
                'date_of_birth' => \App\Support\FormRules::DOB,
                'qualification' => \App\Support\FormRules::TEXT,
                'experience' => \App\Support\FormRules::TEXT,
                'address' => \App\Support\FormRules::TEXT,
                'city' => \App\Support\FormRules::TEXT,
                'state' => \App\Support\FormRules::TEXT,
                'country' => \App\Support\FormRules::TEXT,
                'zip_code' => 'nullable|string|max:20|regex:/^[0-9A-Za-z\-\s]+$/',
            ], \App\Support\FormRules::messages());

            $teacher = Teacher::findOrFail($request->id);
            $teacher->update([
                'full_name' => $request->full_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'qualification' => $request->qualification,
                'experience' => $request->experience,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'country' => $request->country ?: 'Philippines',
            ]);

            User::where('user_id', $teacher->user_id)->update([
                'name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $request->date_of_birth,
            ]);

            Toastr::success('Teacher updated successfully.', 'Success');
            DB::commit();
            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Teacher update failed: '.$e->getMessage());
            Toastr::error('Failed to update teacher.', 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** delete record */
    public function teacherDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            // Find teacher by user_id and delete
            $teacher = Teacher::where('user_id', $request->id)->first();
            if ($teacher) {
                $teacher->delete();
                DB::commit();
                Toastr::success('Deleted record successfully :)','Success');
            } else {
                Toastr::error('Teacher not found :)','Error');
            }
            return redirect()->back();
        } catch(\Exception $e) {
            DB::rollback();
            Log::info($e);
            Toastr::error('Deleted record fail :)','Error');
            return redirect()->back();
        }
    }

    /** Show form to assign grade levels to a teacher */
    public function assignGradeLevelsForm($id)
    {
        $teacher = Teacher::findOrFail($id);
        $assigned = $teacher->gradeLevels->pluck('grade_level')->toArray();
        // Example grade levels, you may want to fetch from a config or table
        $gradeLevels = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
        return view('teacher.assign_grade_levels', compact('teacher', 'gradeLevels', 'assigned'));
    }

    /** Handle assignment of grade levels to a teacher */
    public function assignGradeLevels(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $gradeLevels = $request->input('grade_levels', []);
        // Remove all and re-add
        $teacher->gradeLevels()->delete();
        foreach ($gradeLevels as $level) {
            $teacher->gradeLevels()->create(['grade_level' => $level]);
        }
        return redirect()->route('teacher/list/page')->with('success', 'Grade levels assigned successfully.');
    }

    /** Teacher Information System (TIS) - View teacher details */
    public function viewTIS($user_id)
    {
        // Find user and teacher
        $user = User::where('user_id', $user_id)->firstOrFail();
        $teacher = Teacher::where('user_id', $user_id)->with([
            'subjects',
            'sections',
            'gradeLevels'
        ])->firstOrFail();

        // Get assigned subjects with details
        $assignedSubjects = $teacher->subjects()->with(['sections'])->get();

        // Get assigned sections
        $assignedSections = $teacher->sections;

        // Get grade levels
        $gradeLevels = $teacher->gradeLevels;

        // Get class posts and teaching schedule
        $classPosts = ClassPost::with('subject')
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $teachingSchedule = ClassSchedule::with(['subject', 'section', 'room'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('teacher.tis', compact(
            'user',
            'teacher',
            'assignedSubjects',
            'assignedSections',
            'gradeLevels',
            'classPosts',
            'teachingSchedule'
        ));
    }

    /** Sync all teacher users with teachers table */
    public function syncTeacherUsers()
    {
        try {
            // Get all users with Teacher role
            $teacherUsers = User::where('role_name', 'Teacher')->get();
            $syncedCount = 0;
            $updatedCount = 0;
            
            foreach ($teacherUsers as $user) {
                // Check if teacher record already exists
                $existingTeacher = Teacher::where('user_id', $user->user_id)->first();
                
                if (!$existingTeacher) {
                    // Create teacher record with data from user table
                    Teacher::create([
                        'user_id' => $user->user_id,
                        'full_name' => $user->name,
                        'phone_number' => $user->phone_number ?: 'Not specified',
                        'address' => 'Not specified',
                        'city' => 'Not specified',
                        'state' => 'Not specified',
                        'zip_code' => 'Not specified',
                        'country' => 'Philippines',
                        'gender' => 'Not specified',
                        'date_of_birth' => $user->date_of_birth ?: 'Not specified',
                        'qualification' => 'Not specified',
                        'experience' => 'Not specified',
                        'avatar' => $user->avatar ?: 'photo_defaults.jpg'
                    ]);
                    $syncedCount++;
                } else {
                    // Update existing teacher record with latest user data
                    $existingTeacher->update([
                        'full_name' => $user->name,
                        'phone_number' => $user->phone_number ?: $existingTeacher->phone_number,
                    ]);
                    $updatedCount++;
                }
            }
            
            $message = "Successfully synced $syncedCount new teacher(s)";
            if ($updatedCount > 0) {
                $message .= " and updated $updatedCount existing teacher(s)";
            }
            
            Toastr::success($message, 'Success');
            return redirect()->back();
            
        } catch (\Exception $e) {
            Toastr::error('Failed to sync teacher users: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }
}

