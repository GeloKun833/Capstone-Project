<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Rules\MatchOldPassword;


class UserManagementController extends Controller
{
    /** index page */
    public function index()
    {
        return view('usermanagement.list_users');
    }

    /** Admin: Parent user accounts */
    public function parentList(Request $request)
    {
        $query = User::query()->where('role_name', 'Parent');

        if ($id = $request->get('search_id')) {
            $query->where(function ($q) use ($id) {
                $q->where('user_id', 'like', "%{$id}%")
                    ->orWhere('id', 'like', "%{$id}%");
            });
        }

        if ($name = $request->get('search_name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($email = $request->get('search_email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        if ($phone = $request->get('search_phone')) {
            $query->where('phone_number', 'like', "%{$phone}%");
        }

        $parents = $query->orderBy('name')->paginate(20)->withQueryString();

        $emails = $parents->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)))->unique()->values();
        $childrenByEmail = collect();
        if ($emails->isNotEmpty()) {
            $students = \App\Models\Student::query()
                ->whereNotNull('parent_email')
                ->where('parent_email', '!=', '')
                ->where(function ($q) use ($emails) {
                    foreach ($emails as $email) {
                        $q->orWhereRaw('LOWER(TRIM(parent_email)) = ?', [$email]);
                    }
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'year_level', 'class', 'section', 'parent_email']);

            $childrenByEmail = $students->groupBy(fn ($s) => strtolower(trim((string) $s->parent_email)));
        }

        return view('usermanagement.list_parents', compact('parents', 'childrenByEmail'));
    }

    /** user view */
    public function userView($id)
    {
        $users = User::where('user_id', $id)->first();
        if (!$users) {
            Toastr::error('User not found.', 'Error');
            return redirect()->route('list/users');
        }
        $role = DB::table('role_type_users')->get();
        return view('usermanagement.user_update', compact('users', 'role'));
    }

    /** user Update */
    public function userUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!in_array(Session::get('role_name'), ['Admin', 'Super Admin'], true)) {
                DB::rollBack();
                Toastr::error('You are not allowed to update users.', 'Error');
                return redirect()->back();
            }

            $request->validate([
                'user_id' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone_number' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|string|max:50',
                'status' => 'required|string|max:50',
                'role_name' => 'required|string|max:50',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
                'hidden_avatar' => 'nullable|string|max:255',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            $user = User::where('user_id', $request->user_id)->first();
            if (!$user) {
                DB::rollBack();
                Toastr::error('User not found.', 'Error');
                return redirect()->back();
            }

            $imageName = $user->avatar ?: 'photo_defaults.jpg';
            $uploaded = $request->file('avatar');

            if ($uploaded && $uploaded->isValid()) {
                $newName = time() . '_' . uniqid() . '.' . strtolower($uploaded->getClientOriginalExtension());
                $destination = public_path('images');

                if (!is_dir($destination)) {
                    mkdir($destination, 0755, true);
                }

                $uploaded->move($destination, $newName);

                // Remove old custom avatar safely (never delete the default image)
                $oldName = $request->input('hidden_avatar', $user->avatar);
                if (
                    $oldName
                    && $oldName !== 'photo_defaults.jpg'
                    && $oldName !== $newName
                    && is_file(public_path('images/' . $oldName))
                ) {
                    @unlink(public_path('images/' . $oldName));
                }

                $imageName = $newName;
            }

            // Empty DOB from form should be null (avoids SQL date errors)
            $dob = trim((string) $request->input('date_of_birth', ''));
            if ($dob === '') {
                $dob = null;
            }

            $payload = [
                'name' => $request->name,
                'role_name' => $request->role_name,
                'email' => $request->email,
                'position' => $request->position,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob,
                'department' => $request->department,
                'status' => $request->status,
                'avatar' => $imageName,
            ];

            if ($request->filled('new_password')) {
                $payload['password'] = \Illuminate\Support\Facades\Hash::make($request->new_password);
            }

            $user->update($payload);

            DB::commit();
            Toastr::success('User updated successfully.', 'Success');
            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User update failed: ' . $e->getMessage(), [
                'user_id' => $request->user_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            Toastr::error('User update failed: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /** user delete */
    public function userDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            if (Session::get('role_name') === 'Super Admin' || Session::get('role_name') === 'Admin')
            {
                // Find the user first
                $user = User::where('user_id', $request->user_id)->first();
                
                if (!$user) {
                    Toastr::error('User not found','Error');
                    return redirect()->back();
                }

                // Check if user is trying to delete themselves
                if ($user->id === auth()->id()) {
                    Toastr::error('You cannot delete your own account','Error');
                    return redirect()->back();
                }

                // Check if user is the last admin
                if ($user->role_name === 'Admin' && User::where('role_name', 'Admin')->count() <= 1) {
                    Toastr::error('Cannot delete the last admin user','Error');
                    return redirect()->back();
                }

                // Delete related records first to avoid foreign key constraint issues
                // Delete teacher record if exists
                if ($user->teacher) {
                    $user->teacher->delete();
                }

                // Delete student record if exists
                if ($user->student) {
                    $user->student->delete();
                }

                // Delete messages where user is sender or recipient
                DB::table('messages')->where('sender_id', $user->id)->delete();
                DB::table('messages')->where('recipient_id', $user->id)->delete();

                // Delete class post comments by this user
                DB::table('class_post_comments')->where('user_id', $user->id)->delete();

                // Delete the user
                $user->delete();

                // Delete avatar file if not default
                if ($request->avatar && $request->avatar !== 'photo_defaults.jpg') {
                    $avatarPath = public_path('images/' . $request->avatar);
                    if (file_exists($avatarPath)) {
                        unlink($avatarPath);
                    }
                }
            } else {
                Toastr::error('User deleted fail :)','Error');
                return redirect()->back();
            }

            DB::commit();
            Toastr::success('User deleted successfully :)','Success');
            return redirect()->back();
    
        } catch(\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            DB::rollback();
            Toastr::error('User deleted fail: ' . $e->getMessage(),'Error');
            return redirect()->back();
        }
    }

    /** change password */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'     => ['required', new MatchOldPassword],
            'new_password'         => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        DB::commit();
        Toastr::success('User change successfully :)','Success');
        return redirect()->intended('home');
    }

    /** get users data */
    public function getUsersData(Request $request)
    {
        $draw            = $request->get('draw');
        $start           = $request->get("start");
        $rowPerPage      = $request->get("length"); // total number of rows per page
        $columnIndex_arr = $request->get('order');
        $columnName_arr  = $request->get('columns');
        $order_arr       = $request->get('order');
        $search_arr      = $request->get('search');

        // Custom search parameters
        $searchId = $request->get('search_id');
        $searchName = $request->get('search_name');
        $searchPhone = $request->get('search_phone');

        $columnIndex     = $columnIndex_arr[0]['column']; // Column index
        $columnName      = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue     = $search_arr['value']; // Search value

        $users = DB::table('users');

        $totalRecords = (clone $users)->count();

        // Apply custom search filters
        if (!empty($searchId)) {
            $users->where('user_id', 'like', '%' . $searchId . '%');
        }
        if (!empty($searchName)) {
            $users->where('name', 'like', '%' . $searchName . '%');
        }
        if (!empty($searchPhone)) {
            $users->where('phone_number', 'like', '%' . $searchPhone . '%');
        }

        // Apply DataTable search
        if (!empty($searchValue)) {
            $users->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%');
                $query->orWhere('email', 'like', '%' . $searchValue . '%');
                $query->orWhere('position', 'like', '%' . $searchValue . '%');
                $query->orWhere('phone_number', 'like', '%' . $searchValue . '%');
                $query->orWhere('status', 'like', '%' . $searchValue . '%');
                $query->orWhere('user_id', 'like', '%' . $searchValue . '%');
            });
        }

        $totalRecordsWithFilter = (clone $users)->count();

        if ($columnName == 'name') {
            $columnName = 'name';
        }
        
        $records = $users->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowPerPage)
            ->select('id', 'user_id', 'name', 'email', 'position', 'phone_number', 'join_date', 'status', 'avatar', 'role_name')
            ->get();
        $data_arr = [];
        
        foreach ($records as $key => $record) {
            $modify = '
                <td class="text-right">
                    <div class="dropdown dropdown-action">
                        <a href="" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v ellipse_color"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="'.url('users/add/edit/'.$record->user_id).'">
                                <i class="far fa-edit me-2"></i> Edit
                            </a>
                            <a class="dropdown-item" href="'.url('users/delete/'.$record->id).'">
                            <i class="fas fa-trash-alt m-r-5"></i> Delete
                        </a>
                        </div>
                    </div>
                </td>
            ';
            $avatar = '
                <td>
                    <h2 class="table-avatar">
                        <a class="avatar-sm me-2">
                            <img class="avatar-img rounded-circle avatar" data-avatar='.$record->avatar.' src="/images/'.$record->avatar.'"alt="'.$record->name.'">
                        </a>
                    </h2>
                </td>
            ';
            if ($record->status === 'Active') {
                $status = '<td><span class="badge bg-success-dark">'.$record->status.'</span></td>';
            } elseif ($record->status === 'Disable') {
                $status = '<td><span class="badge bg-danger-dark">'.$record->status.'</span></td>';
            }  elseif ($record->status === 'Inactive') {
                $status = '<td><span class="badge badge-warning">'.$record->status.'</span></td>';
            } else {
                $status = '<td><span class="badge badge-secondary">'.$record->status.'</span></td>';
            }

            // Add SIS button for students
            $sisButton = '';
            if ($record->role_name === 'Student') {
                $sisButton = '
                    <a href="'.url('student/sis/'.$record->user_id).'" class="btn btn-sm bg-success-light" title="View Student Information System">
                        <i class="fas fa-user-graduate me-1"></i> SIS
                    </a>
                ';
            }

            $modify = '
                <td class="text-end"> 
                    <div class="actions">
                        '.$sisButton.'
                        <a href="'.url('view/user/edit/'.$record->user_id).'" class="btn btn-sm bg-danger-light" title="Edit User">
                            <i class="far fa-edit me-2"></i>
                        </a>
                        <a class="btn btn-sm bg-danger-light delete user_id" data-bs-toggle="modal" data-user_id="'.$record->user_id.'" data-bs-target="#delete" title="Delete User">
                            <i class="fe fe-trash-2"></i>
                        </a>
                    </div>
                </td>
            ';
           
            $data_arr [] = [
                "user_id"      => $record->user_id,
                "avatar"       => $avatar,
                "name"         => $record->name,
                "email"        => $record->email,
                "position"     => $record->position,
                "phone_number" => $record->phone_number,
                "join_date"    => $record->join_date,
                "status"       => $status, 
                "modify"       => $modify, 
            ];
        }

        $response = [
            "draw"                 => intval($draw),
            "iTotalRecords"        => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordsWithFilter,
            "aaData"               => $data_arr
        ];
        return response()->json($response);
    }
}
