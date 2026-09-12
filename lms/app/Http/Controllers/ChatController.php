<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Who each role may chat with.
     * Admin: Teachers + Registrars only.
     */
    protected function allowedRolesFor(User $user): array
    {
        return match ($user->role_name) {
            'Admin' => ['Teacher', 'Registrar'],
            'Registrar' => ['Admin', 'Teacher'],
            'Teacher' => ['Admin', 'Registrar', 'Teacher', 'Student', 'Parent'],
            'Student' => ['Teacher'],
            'Parent' => ['Teacher'],
            default => [],
        };
    }

    protected function activeContactsQuery(User $user)
    {
        $roles = $this->allowedRolesFor($user);

        return User::query()
            ->whereIn('role_name', $roles)
            ->where('id', '!=', $user->id)
            ->whereRaw('LOWER(status) = ?', ['active'])
            ->orderBy('role_name')
            ->orderBy('name');
    }

    protected function canChatWith(User $user, User $other): bool
    {
        if ($user->id === $other->id) {
            return false;
        }

        return in_array($other->role_name, $this->allowedRolesFor($user), true);
    }

    /**
     * Display the messenger-style chat interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $contacts = $this->activeContactsQuery($user)->get();

        $conversations = $contacts->map(function ($contact) use ($user) {
            $lastMessage = Message::where(function ($q) use ($user, $contact) {
                $q->where('sender_id', $user->id)->where('recipient_id', $contact->id);
            })->orWhere(function ($q) use ($user, $contact) {
                $q->where('sender_id', $contact->id)->where('recipient_id', $user->id);
            })->orderByDesc('created_at')->first();

            $unreadCount = Message::where('sender_id', $contact->id)
                ->where('recipient_id', $user->id)
                ->where('is_read', false)
                ->count();

            return [
                'user' => $contact,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage?->created_at,
            ];
        })->sortByDesc(function ($row) {
            return optional($row['last_message_time'])->timestamp ?? 0;
        })->values();

        $grouped = $conversations->groupBy(fn ($row) => $row['user']->role_name);

        $receiverId = (int) $request->query('receiver_id', 0);
        if ($receiverId && !$contacts->contains('id', $receiverId)) {
            $receiverId = 0;
        }

        return view('chat.index', compact('conversations', 'grouped', 'receiverId'));
    }

    public function getConversation($userId)
    {
        $user = Auth::user();
        $contact = User::findOrFail($userId);

        if (!$this->canChatWith($user, $contact)) {
            return response()->json(['message' => 'You are not allowed to chat with this user.'], 403);
        }

        $messages = Message::where(function ($q) use ($user, $userId) {
            $q->where('sender_id', $user->id)->where('recipient_id', $userId);
        })->orWhere(function ($q) use ($user, $userId) {
            $q->where('sender_id', $userId)->where('recipient_id', $user->id);
        })
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'asc')
            ->get();

        Message::where('sender_id', $userId)
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'role_name' => $contact->role_name,
                'avatar' => $contact->avatar ?? 'default-avatar.png',
            ],
            'messages' => $messages->map(function ($message) use ($user) {
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
                    'sender_avatar' => $message->sender->avatar ?? 'default-avatar.png',
                ];
            }),
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $recipient = User::findOrFail($request->recipient_id);

        if (!$this->canChatWith($user, $recipient)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to chat with this user.',
            ], 403);
        }

        try {
            $message = Message::create([
                'sender_id' => $user->id,
                'recipient_id' => $recipient->id,
                'content' => $request->content,
                'subject' => 'Chat Message',
                'type' => 'general',
                'priority' => 'normal',
                'is_read' => false,
            ]);

            $message->load('sender');

            try {
                $recipient->notify(new NewMessageNotification($message));
                Cache::forget('header.notifs.' . $recipient->id);
            } catch (\Throwable $e) {
                Log::warning('Chat notification failed: ' . $e->getMessage(), [
                    'message_id' => $message->id,
                    'recipient_id' => $recipient->id,
                ]);
            }

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
                    'sender_avatar' => $user->avatar ?? 'default-avatar.png',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteMessage($messageId)
    {
        try {
            $message = Message::findOrFail($messageId);
            $user = Auth::user();

            if ($message->sender_id == $user->id || $message->recipient_id == $user->id) {
                $message->delete();
                return response()->json(['success' => true, 'message' => 'Message deleted']);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = Message::where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function searchContacts(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->get('search', ''));

        $query = $this->activeContactsQuery($user);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $contacts = $query->limit(20)->get(['id', 'name', 'email', 'role_name', 'avatar']);

        return response()->json(['contacts' => $contacts]);
    }
}
