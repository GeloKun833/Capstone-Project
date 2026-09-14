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
        $role = DB::table('role_type_users')
            ->whereIn('role_type', ['Admin', 'Registrar', 'Teacher', 'Student', 'Parent'])
            ->orderByRaw("FIELD(role_type, 'Admin', 'Registrar', 'Teacher', 'Student', 'Parent')")
            ->get();

        // Ensure Parent always appears even if migration not yet run
        if ($role->where('role_type', 'Parent')->isEmpty()) {
            $role->push((object) ['role_type' => 'Parent']);
        }
        if ($role->where('role_type', 'Teacher')->isEmpty()) {
            $role->push((object) ['role_type' => 'Teacher']);
        }

        $isSoleAdmin = $users->role_name === 'Admin'
            && User::where('role_name', 'Admin')->where('status', 'Active')->count() <= 1;

        return view('usermanagement.user_update', compact('users', 'role', 'isSoleAdmin'));
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
                'name' => \App\Support\FormRules::NAME,
                'email' => 'required|email|max:255',
                'phone_number' => \App\Support\FormRules::PHONE_REQUIRED,
                'date_of_birth' => \App\Support\FormRules::DOB,
                'status' => 'required|string|max:50',
                'role_name' => 'required|string|in:Admin,Registrar,Teacher,Student,Parent',
                'position' => \App\Support\FormRules::TEXT_REQUIRED,
                'department' => \App\Support\FormRules::TEXT_REQUIRED,
                'avatar' => \App\Support\FormRules::AVATAR,
                'hidden_avatar' => 'nullable|string|max:255',
                'new_password' => 'nullable|string|min:8|confirmed',
            ], \App\Support\FormRules::messages());

            $user = User::where('user_id', $request->user_id)->first();
            if (!$user) {
                DB::rollBack();
                Toastr::error('User not found.', 'Error');
                return redirect()->back();
            }

            // Protect the only active Admin from role/status downgrade
            $activeAdmins = User::where('role_name', 'Admin')->where('status', 'Active')->count();
            if ($user->role_name === 'Admin' && $activeAdmins <= 1) {
                if ($request->role_name !== 'Admin') {
                    DB::rollBack();
                    Toastr::error('Cannot change role: this is the only active administrator.', 'Protected');
                    return redirect()->back()->withInput();
                }
                if ($request->status !== 'Active') {
                    DB::rollBack();
                    Toastr::error('Cannot disable the only active administrator.', 'Protected');
                    return redirect()->back()->withInput();
                }
            }

            $imageName = $user->avatar ?: 'photo_defaults.jpg';
            $stored = \App\Support\AvatarUploader::store($request->file('avatar'), $request->input('hidden_avatar', $user->avatar));
            if ($stored) {
                $imageName = $stored;
            }

            $dob = trim((string) $request->input('date_of_birth', ''));
            if ($dob === '') {
                $dob = null;
            }

            $payload = [
                'name' => $request->name,
                'role_name' => $request->role_name,
                'email' => $request->email,
                'position' => $request->position,
                'phone_number' => preg_replace('/[^\d+\-\s()]/', '', (string) $request->phone_number),
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
            Log::error('User update failed: ' . $e->getMessage(), [
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
        $start           = (int) $request->get('start', 0);
        $rowPerPage      = (int) $request->get('length', 10);
        if ($rowPerPage < 1 || $rowPerPage > 100) {
            $rowPerPage = 10;
        }
        $columnIndex_arr = $request->get('order') ?? [['column' => 0, 'dir' => 'asc']];
        $columnName_arr  = $request->get('columns') ?? [];
        $search_arr      = $request->get('search') ?? ['value' => ''];

        $searchId = $request->get('search_id');
        $searchName = $request->get('search_name');
        $searchPhone = $request->get('search_phone');

        $columnIndex     = (int) ($columnIndex_arr[0]['column'] ?? 0);
        $columnName      = $columnName_arr[$columnIndex]['data'] ?? 'user_id';
        $columnSortOrder = $columnIndex_arr[0]['dir'] ?? 'asc';
        $searchValue     = $search_arr['value'] ?? '';

        $allowedSort = ['user_id', 'name', 'email', 'phone_number', 'join_date', 'position', 'status'];
        if (! in_array($columnName, $allowedSort, true)) {
            $columnName = 'user_id';
        }

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
        $fallbackPhoto = asset('images/photo_defaults.jpg');

        foreach ($records as $record) {
            $avatarUrl = \App\Support\AvatarUploader::url($record->avatar);
            $roleLabel = $record->role_name ?: ($record->position ?: 'User');
            $statusKey = strtolower((string) $record->status);
            $statusClass = match ($statusKey) {
                'active' => 'dir-badge--active',
                'inactive' => 'dir-badge--inactive',
                'disable', 'disabled' => 'dir-badge--disabled',
                default => 'dir-badge--neutral',
            };
            $statusLabel = $record->status ? ucfirst($statusKey) : '—';

            $name = '<div class="dir-person">'
                .'<img src="'.e($avatarUrl).'" alt="'.e($record->name).'" data-avatar="'.e($record->avatar).'" onerror="this.onerror=null;this.src=\''.e($fallbackPhoto).'\';">'
                .'<span>'
                .'<a class="dir-person-name" href="'.url('view/user/edit/'.$record->user_id).'">'.e($record->name).'</a>'
                .'<span class="dir-person-meta">'.e($roleLabel).'</span>'
                .'</span>'
                .'</div>';

            $sisButton = '';
            if ($record->role_name === 'Student') {
                $sisButton = '<a href="'.url('student/sis/'.$record->user_id).'" class="dir-icon-btn is-success" title="Student Information System"><i class="fas fa-id-card"></i></a>';
            }

            $modify = '<div class="d-inline-flex gap-1 justify-content-end">'
                .$sisButton
                .'<a href="'.url('view/user/edit/'.$record->user_id).'" class="dir-icon-btn" title="Edit user"><i class="far fa-edit"></i></a>'
                .'<a class="dir-icon-btn is-danger delete" data-bs-toggle="modal" data-bs-target="#delete" data-user_id="'.e($record->user_id).'" data-avatar="'.e($record->avatar).'" title="Delete user"><i class="far fa-trash-alt"></i></a>'
                .'</div>';

            $data_arr[] = [
                'user_id' => '<span class="text-muted">'.e($record->user_id).'</span>',
                'name' => $name,
                'email' => e($record->email ?: '—'),
                'phone_number' => e($record->phone_number ?: '—'),
                'join_date' => e($record->join_date ?: '—'),
                'position' => $record->position
                    ? '<span class="dir-chip dir-chip--soft">'.e($record->position).'</span>'
                    : '<span class="dir-muted">—</span>',
                'status' => '<span class="dir-badge '.$statusClass.'">'.e($statusLabel).'</span>',
                'modify' => $modify,
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
