<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\GradeEncoding;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class GradeEncodingAccessController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    public function index()
    {
        $users = User::query()
            ->with('permissions')
            ->whereIn('role_name', [User::ROLE_TEACHER, User::ROLE_REGISTRAR])
            ->orderBy('role_name')
            ->orderBy('name')
            ->get();

        return view('admin.grade-encoding.index', [
            'users' => $users,
            'permission' => GradeEncoding::PERMISSION,
        ]);
    }

    public function update(Request $request, User $user)
    {
        if (! in_array($user->role_name, [User::ROLE_TEACHER, User::ROLE_REGISTRAR], true)) {
            return back()->with('error', 'Grade-encoding access can only be changed for teachers and registrars.');
        }

        $allow = $request->boolean('allow');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($allow) {
            $user->givePermissionTo(GradeEncoding::PERMISSION);
            $message = $user->name.' can now encode grades.';
        } else {
            $user->revokePermissionTo(GradeEncoding::PERMISSION);
            $message = $user->name.' can no longer encode grades.';
        }

        return back()->with('success', $message);
    }
}
