<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Admin,Teacher')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get announcements based on user role
        $announcements = Announcement::active()
            ->forRole($user->role_name)
            ->with('creator')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::all();
        $roles = ['students', 'teachers', 'parents', 'admins'];
        
        return view('announcements.create', compact('sections', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
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
            'is_pinned' => 'boolean',
            'is_scheduled' => 'boolean',
            'scheduled_at' => 'nullable|date|after:now',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'priority' => $request->priority,
            'target_audience' => $request->target_audience,
            'target_roles' => $request->target_roles,
            'target_sections' => $request->target_sections,
            'is_pinned' => $request->has('is_pinned'),
            'is_scheduled' => $request->has('is_scheduled'),
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
            'created_by' => Auth::id(),
        ]);

        // Send notifications to target users
        $this->sendAnnouncementNotifications($announcement);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $announcement = Announcement::with('creator')->findOrFail($id);
        
        // Check if user can view this announcement
        if (!$announcement->isVisibleTo(Auth::user())) {
            abort(403, 'You do not have permission to view this announcement.');
        }

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Check if user can edit this announcement
        if (Auth::user()->role_name !== 'Admin' && $announcement->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to edit this announcement.');
        }

        $sections = Section::all();
        $roles = ['students', 'teachers', 'parents', 'admins'];
        
        return view('announcements.edit', compact('announcement', 'sections', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Check if user can edit this announcement
        if (Auth::user()->role_name !== 'Admin' && $announcement->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to edit this announcement.');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,academic,event,reminder,emergency',
            'priority' => 'required|in:low,normal,high,urgent',
            'target_audience' => 'required|in:all,students,teachers,parents,admins',
            'target_roles' => 'nullable|array',
            'target_sections' => 'nullable|array',
            'is_pinned' => 'boolean',
            'is_scheduled' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'priority' => $request->priority,
            'target_audience' => $request->target_audience,
            'target_roles' => $request->target_roles,
            'target_sections' => $request->target_sections,
            'is_pinned' => $request->has('is_pinned'),
            'is_scheduled' => $request->has('is_scheduled'),
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Check if user can delete this announcement
        if (Auth::user()->role_name !== 'Admin' && $announcement->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to delete this announcement.');
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    /**
     * Toggle pin status of announcement
     */
    public function togglePin(string $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        // Only admins can pin/unpin announcements
        if (Auth::user()->role_name !== 'Admin') {
            abort(403, 'You do not have permission to pin/unpin announcements.');
        }

        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return redirect()->back()
            ->with('success', 'Announcement pin status updated!');
    }

    /**
     * Get announcements for dashboard widget
     */
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

    /**
     * Send notifications to target users
     */
    private function sendAnnouncementNotifications($announcement)
    {
        // Get target users based on announcement settings
        $query = User::query();

        if ($announcement->target_audience !== 'all') {
            $query->where('role_name', $announcement->target_audience);
        }

        if ($announcement->target_roles) {
            $query->whereIn('role_name', $announcement->target_roles);
        }

        $users = $query->get();

        // Send notifications via Laravel notification system (database + email)
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\AnnouncementNotification($announcement));
        }
    }
}
