<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Models\User;
use App\Services\GradeSubjectCatalogService;
use App\Support\GradeEncoding;
use Carbon\Carbon;
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
            ->where('role_name', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get();

        $today = now('Asia/Manila')->toDateString();
        $savedStart = GradeEncoding::startsOn();

        return view('admin.grade-encoding.index', [
            'users' => $users,
            'permission' => GradeEncoding::PERMISSION,
            'gradeLevels' => GradeSubjectCatalogService::gradeLevels(),
            'openLevels' => GradeEncoding::openLevels(),
            'hasWindow' => GradeEncoding::hasWindow(),
            'windowNote' => GradeEncoding::windowNote(),
            'startsOn' => $savedStart,
            'dueOn' => GradeEncoding::dueOn(),
            'schoolToday' => $today,
            'startMin' => ($savedStart && $savedStart < $today) ? $savedStart : $today,
        ]);
    }

    public function updateWindow(Request $request)
    {
        $request->validate([
            'encoding_starts_on' => ['required', 'date'],
            'encoding_due_on' => ['required', 'date'],
        ], [
            'encoding_starts_on.required' => 'Enter the opening date.',
            'encoding_due_on.required' => 'Enter the due date.',
        ]);

        $today = now('Asia/Manila')->toDateString();
        $start = Carbon::parse($request->input('encoding_starts_on'), 'Asia/Manila')->toDateString();
        $due = Carbon::parse($request->input('encoding_due_on'), 'Asia/Manila')->toDateString();
        $settings = SchoolSetting::getSettings();
        $savedStart = $settings->encoding_starts_on?->toDateString();

        if ($start < $today && $start !== $savedStart) {
            return back()->withInput()->withErrors([
                'encoding_starts_on' => 'The opening date cannot be earlier than today.',
            ]);
        }
        if ($due < $start) {
            return back()->withInput()->withErrors([
                'encoding_due_on' => 'The due date must be on or after the opening date.',
            ]);
        }
        if ($due < $today) {
            return back()->withInput()->withErrors([
                'encoding_due_on' => 'The due date cannot be earlier than today.',
            ]);
        }

        $settings->encoding_starts_on = $start;
        $settings->encoding_due_on = $due;
        $settings->save();
        SchoolSetting::clearSettingsCache();

        return back()->with('success', 'Grade entry is set from '.Carbon::parse($start)->format('M j, Y').' through '.Carbon::parse($due)->format('M j, Y').'.');
    }

    public function allowAll()
    {
        if (! GradeEncoding::hasWindow()) {
            return back()->with('error', 'Set the opening date and the due date before allowing teachers to encode grades.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $teachers = User::query()->where('role_name', User::ROLE_TEACHER)->get();
        foreach ($teachers as $teacher) {
            $teacher->givePermissionTo(GradeEncoding::PERMISSION);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $count = $teachers->count();

        return back()->with('success', $count === 0
            ? 'There are no teachers to allow.'
            : 'All '.$count.' teacher'.($count === 1 ? '' : 's').' can now encode grades.');
    }

    public function updateLevels(Request $request)
    {
        $valid = GradeSubjectCatalogService::gradeLevels();
        $selected = array_values(array_intersect($valid, (array) $request->input('grade_levels', [])));

        $settings = SchoolSetting::getSettings();
        $settings->encoding_open_grades = $selected;
        $settings->save();
        SchoolSetting::clearSettingsCache();

        $count = count($selected);

        return back()->with('success', $count === 0
            ? 'Grade encoding is closed for every grade level.'
            : 'Grade encoding is open for '.$count.' grade level'.($count === 1 ? '' : 's').'.');
    }

    public function update(Request $request, User $user)
    {
        if ($user->role_name !== User::ROLE_TEACHER) {
            return back()->with('error', 'Grade-encoding access can only be changed for a teacher.');
        }

        $allow = $request->boolean('allow');
        if ($allow && ! GradeEncoding::hasWindow()) {
            return back()->with('error', 'Set the opening date and the due date before allowing a teacher to encode grades.');
        }

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
