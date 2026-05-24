<?php
// app/Http/Controllers/NotificationController.php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications (role-based)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Notification::with(['flock', 'user'])
            ->where('user_id', $user->id);  // Only show user's own notifications
        
        if ($request->get('unread_only')) {
            $query->whereNull('read_at');
        }
        
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }
    
    /**
     * Display the specified notification
     */
    public function show($id)
    {
        $notification = Notification::with(['flock', 'user'])->findOrFail($id);
        $user = auth()->user();
        
        // Check if user owns this notification or is admin
        if ($notification->user_id !== $user->id && $user->role !== 'admin') {
            abort(403, 'You are not authorized to view this notification.');
        }
        
        // Mark as read if not already
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        
        return view('notifications.show', compact('notification'));
    }
    
    /**
     * Mark a notification as read via AJAX
     */
    public function markAsReadAjax($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $user = auth()->user();
            
            if ($notification->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            if (!$notification->read_at) {
                $notification->update(['read_at' => now()]);
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('notifications.index')
            ->with('success', 'All notifications marked as read');
    }
    
    /**
     * Remove the specified notification
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $user = auth()->user();
        
        if ($user->role !== 'admin' && $notification->user_id !== $user->id) {
            abort(403);
        }
        
        $notification->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'Notification deleted successfully');
    }
    
    /**
     * Clear all notifications for current user
     */
    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'All notifications cleared');
    }
    
    /**
     * Get unread notifications count (for AJAX)
     */
    public function unreadCount()
    {
        $user = auth()->user();
        
        $count = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Get recent notifications for header dropdown
     */
    public function apiNotifications(Request $request)
    {
        try {
            $user = auth()->user();
            
            $notifications = Notification::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get()
                ->map(function($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'severity' => $notification->severity,
                        'read_at' => $notification->read_at,
                        'time_ago' => $notification->created_at->diffForHumans(),
                        'created_at' => $notification->created_at->format('d M Y, h:i A')
                    ];
                });
            
            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }
    
    /**
     * Get notification details as JSON for modal
     */
    public function getNotificationJson($id)
    {
        try {
            $notification = Notification::with(['flock', 'user'])->findOrFail($id);
            $user = auth()->user();
            
            if ($notification->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            
            // Mark as read if not already
            if (!$notification->read_at) {
                $notification->update(['read_at' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'severity' => $notification->severity,
                    'created_at' => $notification->created_at->format('d M Y, h:i A'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                    'flock' => $notification->flock ? [
                        'id' => $notification->flock->id,
                        'flock_number' => $notification->flock->flock_number
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}