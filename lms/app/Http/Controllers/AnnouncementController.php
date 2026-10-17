<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\GradeSubjectCatalogService;
use App\Services\TeacherClassAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin|Teacher')->only(['create', 'store', 'edit', 'update', 'destroy', 'togglePin']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Admin manages everything; others only see visible/active for their role
        if ($user->role_name === 'Admin') {
            $query = Announcement::with('creator');
        } else {
            $query = Announcement::active()->visibleToUser($user)->with('creator');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->get('status') === 'pinned') {
            $query->where('is_pinned', true);
        } elseif ($request->get('status') === 'active') {
            $query->where('is_active', true);
        }

        $announcements = $query
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('announcements.create', [
            'sections' => $this->audienceSections(Auth::user()),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'integer',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|extensions:pdf,docx,jpg,jpeg,png,gif,webp,txt,xls,xlsx,ppt,pptx',
        ], [
            'attachments.*.extensions' => 'Please upload PDF, Word (DOCX), Excel, PowerPoint, text, or image files.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->filled('expires_at') && ! Carbon::parse($request->expires_at, $this->schoolTimezone())->gt(Carbon::now($this->schoolTimezone()))) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiration must be set to a future date and time.'])->withInput();
        }

        $sectionIds = $this->selectedSections($request);
        if ($sectionIds === []) {
            return redirect()->back()->withErrors(['section_ids' => 'Choose a grade and section assigned to you.'])->withInput();
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => 'general',
            'priority' => $request->priority,
            'target_audience' => 'sections',
            'target_roles' => null,
            'target_sections' => $sectionIds,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_scheduled' => $request->boolean('is_scheduled') || $request->filled('scheduled_at'),
            'scheduled_at' => $this->toUtcDateTime($request->input('scheduled_at')),
            'expires_at' => $this->toUtcDateTime($request->input('expires_at')),
            'attachments' => $this->storeAttachments($request),
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        if (!$announcement->is_scheduled || !$announcement->scheduled_at || $announcement->scheduled_at->isPast()) {
            $this->sendAnnouncementNotifications($announcement);
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('creator');
        $user = Auth::user();
        $isTeacherCreator = $user->role_name === 'Teacher'
            && (int) $announcement->created_by === (int) $user->id;

        if ($user->role_name !== 'Admin' && ! $isTeacherCreator && ! $announcement->isVisibleTo($user)) {
            abort(403, 'You do not have permission to view this announcement.');
        }

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeManage($announcement);

        return view('announcements.edit', [
            'announcement' => $announcement,
            'sections' => $this->audienceSections(Auth::user()),
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeManage($announcement);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'section_ids' => 'required|array|min:1',
            'section_ids.*' => 'integer',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|extensions:pdf,docx,jpg,jpeg,png,gif,webp,txt,xls,xlsx,ppt,pptx',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'string',
        ], [
            'attachments.*.extensions' => 'Please upload PDF, Word (DOCX), Excel, PowerPoint, text, or image files.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $sectionIds = $this->selectedSections($request);
        if ($sectionIds === []) {
            return redirect()->back()->withErrors(['section_ids' => 'Choose a grade and section assigned to you.'])->withInput();
        }

        $files = collect($announcement->attachments ?? []);

        // Remove selected existing attachments
        $remove = $request->input('remove_attachments', []);
        if (!empty($remove)) {
            $files = $files->reject(function ($file) use ($remove) {
                $path = is_array($file) ? ($file['path'] ?? '') : '';
                if ($path && in_array($path, $remove, true)) {
                    Storage::disk('public')->delete($path);
                    return true;
                }
                return false;
            })->values();
        }

        $newFiles = $this->storeAttachments($request);
        $files = $files->concat($newFiles)->take(5)->values()->all();

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'priority' => $request->priority,
            'target_audience' => 'sections',
            'target_roles' => null,
            'target_sections' => $sectionIds,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_scheduled' => $request->boolean('is_scheduled') || $request->filled('scheduled_at'),
            'scheduled_at' => $this->toUtcDateTime($request->input('scheduled_at')),
            'expires_at' => $this->toUtcDateTime($request->input('expires_at')),
            'attachments' => $files,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorizeManage($announcement);

        foreach ($announcement->attachments ?? [] as $file) {
            $path = is_array($file) ? ($file['path'] ?? null) : null;
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    public function togglePin(Announcement $announcement)
    {
        if (Auth::user()->role_name !== 'Admin') {
            abort(403, 'You do not have permission to pin/unpin announcements.');
        }

        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'is_pinned' => $announcement->is_pinned,
            ]);
        }

        return redirect()->back()->with('success', 'Announcement pin status updated!');
    }

    public function getDashboardAnnouncements()
    {
        $user = Auth::user();

        $announcements = Announcement::active()
            ->visibleToUser($user)
            ->with('creator')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($announcements);
    }

    protected function authorizeManage(Announcement $announcement): void
    {
        $user = Auth::user();
        if ($user->role_name === 'Admin') {
            return;
        }
        if ($user->role_name === 'Teacher' && (int) $announcement->created_by === (int) $user->id) {
            return;
        }
        abort(403, 'You do not have permission to manage this announcement.');
    }

    protected function storeAttachments(Request $request): array
    {
        $saved = [];
        if (!$request->hasFile('attachments')) {
            return $saved;
        }

        foreach ($request->file('attachments') as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }
            $path = $file->store('announcements/' . date('Y/m'), 'public');
            $saved[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'ext' => strtolower($file->getClientOriginalExtension()),
            ];
        }

        return $saved;
    }

    private function sendAnnouncementNotifications(Announcement $announcement)
    {
        try {
            $announcement->loadMissing('creator');
            $users = $this->recipientUsers($announcement);
            if ($users->isEmpty()) {
                return;
            }

            // After response — announcement create/update stays fast on Render+Aiven.
            $announcementId = $announcement->id;
            dispatch(function () use ($users, $announcementId) {
                $announcement = Announcement::with('creator')->find($announcementId);
                if (! $announcement) {
                    return;
                }
                foreach ($users as $user) {
                    $user->notify(new \App\Notifications\AnnouncementNotification($announcement));
                    Cache::forget('header.notifs.'.$user->id);
                }
            })->afterResponse();

            Log::info('Announcement notifications queued after response', [
                'announcement_id' => $announcement->id,
                'recipients' => $users->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed sending announcement notifications: ' . $e->getMessage());
        }
    }

    private function audienceSections(User $user)
    {
        $year = AcademicYear::active();
        $gradeOrder = array_flip(GradeSubjectCatalogService::gradeLevels());

        if ($user->role_name === 'Teacher') {
            $ids = Announcement::sectionIdsFor($user);
            $sections = Section::query()->whereIn('id', $ids ?: [0])->get();
        } else {
            $sections = Section::query()
                ->when($year, fn ($query) => $query->forAcademicYear($year->id))
                ->get();
        }

        return $sections
            ->sortBy(fn (Section $section) => sprintf('%03d-%s', $gradeOrder[$section->grade_level] ?? 999, $section->name))
            ->values();
    }

    private function selectedSections(Request $request): array
    {
        $allowed = $this->audienceSections(Auth::user())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $picked = array_values(array_unique(array_map('intval', (array) $request->input('section_ids', []))));

        return array_values(array_intersect($picked, $allowed));
    }

    private function recipientUsers(Announcement $announcement)
    {
        $sectionIds = array_values(array_filter(array_map('intval', $announcement->target_sections ?? [])));
        if ($sectionIds === []) {
            $roles = $announcement->recipientRoleNames();

            return User::query()->whereIn('role_name', $roles ?: ['__none__'])->get();
        }

        $yearId = AcademicYear::active()?->id;
        $studentIds = DB::table('student_section_assignments')
            ->whereIn('section_id', $sectionIds)
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->pluck('student_id');

        $students = Student::query()
            ->whereIn('id', $studentIds->isEmpty() ? [0] : $studentIds)
            ->get(['user_id', 'parent_user_id']);

        $userIds = $students->pluck('parent_user_id')->filter()->map(fn ($id) => (int) $id)->all();
        $studentUserIds = $students->pluck('user_id')->filter()->unique();
        if ($studentUserIds->isNotEmpty()) {
            $userIds = array_merge($userIds, User::query()->whereIn('user_id', $studentUserIds)->pluck('id')->all());
        }

        $teacherIds = DB::table('section_teacher')
            ->whereIn('section_id', $sectionIds)
            ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
            ->pluck('teacher_id');

        $grades = Section::query()->whereIn('id', $sectionIds)->pluck('grade_level')->filter()->unique();
        $aliases = [];
        foreach ($grades as $grade) {
            foreach (GradeSubjectCatalogService::gradeAliases((string) $grade) as $alias) {
                $aliases[] = $alias;
            }
        }
        if ($aliases !== []) {
            $teacherIds = $teacherIds->merge(
                DB::table('teacher_grade_level')
                    ->whereIn('grade_level', array_values(array_unique($aliases)))
                    ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
                    ->pluck('teacher_id')
            );
        }

        $teacherIds = $teacherIds->merge(
            DB::table('class_schedules')
                ->whereIn('section_id', $sectionIds)
                ->where('is_active', true)
                ->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))
                ->pluck('teacher_id')
        )->filter()->unique();

        if ($teacherIds->isNotEmpty()) {
            $teacherUserIds = DB::table('teachers')->whereIn('id', $teacherIds)->pluck('user_id');
            if ($teacherUserIds->isNotEmpty()) {
                $userIds = array_merge($userIds, User::query()->whereIn('user_id', $teacherUserIds)->pluck('id')->all());
            }
        }

        $userIds = array_values(array_unique(array_filter($userIds, fn ($id) => (int) $id !== (int) $announcement->created_by)));

        return User::query()->whereIn('id', $userIds ?: [0])->get();
    }

    private function schoolTimezone(): string
    {
        return config('app.school_timezone', 'Asia/Manila');
    }

    private function toUtcDateTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value, $this->schoolTimezone())
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }
}
