<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
            $query = Announcement::active()->forRole($user->role_name)->with('creator');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        $sections = Section::orderBy('name')->get();
        $roles = ['students', 'teachers', 'parents', 'admins'];

        return view('announcements.create', compact('sections', 'roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,reminder,emergency',
            'priority' => 'required|in:low,normal,high,urgent',
            'target_audience' => 'required|in:all,students,teachers,parents,admins',
            'target_roles' => 'nullable|array',
            'target_sections' => 'nullable|array',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:now',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp,txt,xls,xlsx,ppt,pptx',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'priority' => $request->priority,
            'target_audience' => $request->target_audience,
            'target_roles' => $request->target_roles,
            'target_sections' => $request->target_sections,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_scheduled' => $request->boolean('is_scheduled') || $request->filled('scheduled_at'),
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
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

        if (Auth::user()->role_name !== 'Admin' && !$announcement->isVisibleTo(Auth::user())) {
            abort(403, 'You do not have permission to view this announcement.');
        }

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $this->authorizeManage($announcement);

        $sections = Section::orderBy('name')->get();
        $roles = ['students', 'teachers', 'parents', 'admins'];

        return view('announcements.edit', compact('announcement', 'sections', 'roles'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeManage($announcement);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,reminder,emergency',
            'priority' => 'required|in:low,normal,high,urgent',
            'target_audience' => 'required|in:all,students,teachers,parents,admins',
            'target_roles' => 'nullable|array',
            'target_sections' => 'nullable|array',
            'is_pinned' => 'nullable|boolean',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp,txt,xls,xlsx,ppt,pptx',
            'remove_attachments' => 'nullable|array',
            'remove_attachments.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
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
            'type' => $request->type,
            'priority' => $request->priority,
            'target_audience' => $request->target_audience,
            'target_roles' => $request->target_roles,
            'target_sections' => $request->target_sections,
            'is_pinned' => $request->boolean('is_pinned'),
            'is_scheduled' => $request->boolean('is_scheduled') || $request->filled('scheduled_at'),
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
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
            ->forRole($user->role_name)
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
            $roleNames = $announcement->recipientRoleNames();
            if (empty($roleNames)) {
                return;
            }

            $users = User::query()->whereIn('role_name', $roleNames)->get();

            foreach ($users as $user) {
                $user->notify(new \App\Notifications\AnnouncementNotification($announcement));
                Cache::forget('header.notifs.' . $user->id);
            }

            Log::info('Announcement notifications sent', [
                'announcement_id' => $announcement->id,
                'roles' => $roleNames,
                'recipients' => $users->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed sending announcement notifications: ' . $e->getMessage());
        }
    }
}
