<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the messenger-style chat interface
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all users the current user can chat with
        if ($user->role_name === 'Admin') {
            $contacts = User::whereIn('role_name', ['Teacher', 'Admin', 'Student', 'Parent'])
                ->where('id', '!=', $user->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } elseif ($user->role_name === 'Teacher') {
            $contacts = User::whereIn('role_name', ['Admin', 'Teacher', 'Student', 'Parent'])
                ->where('id', '!=', $user->id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } elseif ($user->role_name === 'Student') {
            $contacts = User::whereIn('role_name', ['Admin', 'Teacher'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } elseif ($user->role_name === 'Parent') {
            $contacts = User::whereIn('role_name', ['Admin', 'Teacher'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $contacts = collect();
        }

        // Get conversations with last message and unread count
        $conversations = $contacts->map(function($contact) use ($user) {
            $lastMessage = Message::where(function($q) use ($user, $contact) {
                $q->where('sender_id', $user->id)->where('recipient_id', $contact->id);
            })->orWhere(function($q) use ($user, $contact) {
                $q->where('sender_id', $contact->id)->where('recipient_id', $user->id);
            })->orderBy('created_at', 'desc')->first();

            $unreadCount = Message::where('sender_id', $contact->id)
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'user' => $contact,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null
            ];
        })->sortByDesc('last_message_time')->values();

        return view('chat.index', compact('conversations'));
    }

    /**
     * Get conversation messages with a specific user
     */
    public function getConversation($userId)
    {
        $user = Auth::user();
        $contact = User::findOrFail($userId);

        // Get all messages between these two users
        $messages = Message::where(function($q) use ($user, $userId) {
            $q->where('sender_id', $user->id)->where('recipient_id', $userId);
        })->orWhere(function($q) use ($user, $userId) {
            $q->where('sender_id', $userId)->where('recipient_id', $user->id);
        })
        ->with(['sender', 'recipient'])
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark received messages as read
        Message::where('sender_id', $userId)
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'contact' => $contact,
            'messages' => $messages->map(function($message) use ($user) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'is_own' => $message->sender_id == $user->id,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $message->created_at->diffForHumans(),
                    'is_read' => $message->is_read,
                    'sender_name' => $message->sender->name,
                    'sender_avatar' => $message->sender->avatar ?? 'default-avatar.png'
                ];
            })
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000'
        ]);

        $user = Auth::user();

        try {
            $message = Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $request->recipient_id,
                'content' => $request->content,
                'subject' => 'Chat Message', // Default subject for chat messages
                'type' => 'general',
                'priority' => 'normal',
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sender_id' => $message->sender_id,
                    'recipient_id' => $message->recipient_id,
                    'is_own' => true,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $message->created_at->diffForHumans(),
                    'is_read' => false,
                    'sender_name' => $user->name,
                    'sender_avatar' => $user->avatar ?? 'default-avatar.png'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage($messageId)
    {
        try {
            $message = Message::findOrFail($messageId);
            $user = Auth::user();

            // Only allow sender or recipient to delete
            if ($message->sender_id == $user->id || $message->recipient_id == $user->id) {
                $message->delete();
                return response()->json(['success' => true, 'message' => 'Message deleted']);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = Message::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Search contacts
     */
    public function searchContacts(Request $request)
    {
        $user = Auth::user();
        $search = $request->get('search', '');

        if ($user->role_name === 'Admin') {
            $contacts = User::whereIn('role_name', ['Teacher', 'Admin', 'Student', 'Parent'])
                ->where('id', '!=', $user->id)
                ->where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get();
        } elseif (in_array($user->role_name, ['Teacher', 'Student', 'Parent'])) {
            $roles = $user->role_name === 'Teacher'
                ? ['Admin', 'Teacher', 'Student', 'Parent']
                : ['Admin', 'Teacher'];
            $contacts = User::whereIn('role_name', $roles)
                ->where('id', '!=', $user->id)
                ->where('status', 'active')
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->limit(10)
                ->get();
        } else {
            $contacts = collect();
        }

        return response()->json(['contacts' => $contacts]);
    }
}
