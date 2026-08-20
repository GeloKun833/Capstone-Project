<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource (inbox).
     */
    public function index()
    {
        $user = Auth::user();
        
        $messages = Message::forUser($user->id)
            ->notArchived()
            ->with(['sender', 'student'])
            ->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $unreadCount = Message::forUser($user->id)->unread()->count();

        return view('messages.index', compact('messages', 'unreadCount'));
    }

    /**
     * Show sent messages.
     */
    public function sent()
    {
        $user = Auth::user();
        
        $messages = Message::fromUser($user->id)
            ->notArchived()
            ->with(['recipient', 'student'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('messages.sent', compact('messages'));
    }

    /**
     * Show archived messages.
     */
    public function archived()
    {
        $user = Auth::user();
        
        $messages = Message::where('recipient_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->archived()
            ->with(['sender', 'recipient', 'student'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('messages.archived', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $recipients = [];
        $students = [];

        // Get recipients based on user role
        if ($user->role_name === 'Teacher') {
            // Teachers can message parents and students
            $recipients = User::whereIn('role_name', ['Parent', 'Student'])->get();
            $students = Student::all();
        } elseif ($user->role_name === 'Parent') {
            // Parents can message teachers
            $recipients = User::where('role_name', 'Teacher')->get();
            $students = Student::where('parent_email', $user->email)->get();
        } elseif ($user->role_name === 'Admin') {
            // Admins can message everyone
            $recipients = User::where('id', '!=', $user->id)->get();
            $students = Student::all();
        }

        return view('messages.create', compact('recipients', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_id' => 'required|exists:users,id',
            'student_id' => 'nullable|exists:students,id',
            'type' => 'required|in:general,academic,behavioral,attendance,grade',
            'priority' => 'required|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $message = Message::create([
            'subject' => $request->subject,
            'content' => $request->content,
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'student_id' => $request->student_id,
            'type' => $request->type,
            'priority' => $request->priority,
        ]);

        // Send notification to recipient (database + email)
        $recipient = User::find($request->recipient_id);
        if ($recipient) {
            $recipient->notify(new \App\Notifications\NewMessageNotification($message));
        }

        return redirect()->route('messages.index')
            ->with('success', 'Message sent successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = Message::with(['sender', 'recipient', 'student'])->findOrFail($id);
        $user = Auth::user();

        // Check if user can view this message
        if ($message->sender_id !== $user->id && $message->recipient_id !== $user->id) {
            abort(403, 'You do not have permission to view this message.');
        }

        // Mark as read if user is the recipient
        if ($message->recipient_id === $user->id && !$message->is_read) {
            $message->markAsRead();
        }

        return view('messages.show', compact('message'));
    }

    /**
     * Show conversation between two users.
     */
    public function conversation($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);

        // Check if user can have conversation with this user
        if (!$this->canMessageUser($currentUser, $otherUser)) {
            abort(403, 'You do not have permission to message this user.');
        }

        $messages = Message::getConversation($currentUser->id, $userId)
            ->with(['sender', 'recipient', 'student'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark unread messages as read
        Message::where('sender_id', $userId)
            ->where('recipient_id', $currentUser->id)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return view('messages.conversation', compact('messages', 'otherUser'));
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(string $id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->recipient_id === Auth::id()) {
            $message->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 403);
    }

    /**
     * Mark message as unread.
     */
    public function markAsUnread(string $id)
    {
        $message = Message::findOrFail($id);
        
        if ($message->recipient_id === Auth::id()) {
            $message->markAsUnread();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 403);
    }

    /**
     * Archive message.
     */
    public function archive(string $id)
    {
        $message = Message::findOrFail($id);
        $user = Auth::user();
        
        if ($message->sender_id === $user->id || $message->recipient_id === $user->id) {
            $message->archive();
            return redirect()->back()->with('success', 'Message archived successfully!');
        }

        return redirect()->back()->with('error', 'You do not have permission to archive this message.');
    }

    /**
     * Unarchive message.
     */
    public function unarchive(string $id)
    {
        $message = Message::findOrFail($id);
        $user = Auth::user();
        
        if ($message->sender_id === $user->id || $message->recipient_id === $user->id) {
            $message->unarchive();
            return redirect()->back()->with('success', 'Message unarchived successfully!');
        }

        return redirect()->back()->with('error', 'You do not have permission to unarchive this message.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = Message::findOrFail($id);
        $user = Auth::user();
        
        if ($message->sender_id === $user->id || $message->recipient_id === $user->id) {
            $message->delete();
            return redirect()->back()->with('success', 'Message deleted successfully!');
        }

        return redirect()->back()->with('error', 'You do not have permission to delete this message.');
    }

    /**
     * Get unread message count for dashboard.
     */
    public function getUnreadCount()
    {
        $count = Message::forUser(Auth::id())->unread()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Check if user can message another user.
     */
    private function canMessageUser($currentUser, $targetUser)
    {
        // Admins can message anyone
        if ($currentUser->role_name === 'Admin') {
            return true;
        }

        // Teachers can message parents and students
        if ($currentUser->role_name === 'Teacher') {
            return in_array($targetUser->role_name, ['Parent', 'Student']);
        }

        // Parents can message teachers
        if ($currentUser->role_name === 'Parent') {
            return $targetUser->role_name === 'Teacher';
        }

        // Students can message teachers
        if ($currentUser->role_name === 'Student') {
            return $targetUser->role_name === 'Teacher';
        }

        return false;
    }
}
