<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return response()->json(['success' => true]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Display all notifications
     */
    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                abort(401, 'User not authenticated');
            }
            
            // Debug: Check if user has notification methods
            if (!method_exists($user, 'notifications')) {
                \Log::error('User model does not have notifications method');
                $notifications = collect([])->paginate(20);
                return view('notifications.index', compact('notifications'));
            }
            
            $notifications = $user->notifications()->paginate(20);
            
            return view('notifications.index', compact('notifications'));
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Notification error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // If there's an error, return empty notifications
            $notifications = collect([])->paginate(20);
            
            return view('notifications.index', compact('notifications'));
        }
    }
} 