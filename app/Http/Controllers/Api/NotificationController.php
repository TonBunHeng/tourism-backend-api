<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::query();

        // If not admin, restrict to notifications belonging to this user or public platform announcements
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('category', ['System', 'Events', 'Offers', 'General']);
                  });
            });
        } else {
            // Admin sees system-wide (user_id is null) plus admin-directed notifications
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            });
        }

        // Category filter
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        // Unread only filter
        if ($request->boolean('unread_only')) {
            $query->where('read', false);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Summary counts
        $baseQuery = Notification::query();
        if (!$isAdmin) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('category', ['System', 'Events', 'Offers', 'General']);
                  });
            });
        } else {
            $baseQuery->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            });
        }

        $unreadCount = (clone $baseQuery)->where('read', false)->count();
        $totalCount = (clone $baseQuery)->count();

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 50))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'meta' => [
                'total' => $totalCount,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::where('read', false);

        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('user_id')
                          ->whereIn('category', ['System', 'Events', 'Offers', 'General']);
                  });
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            });
        }

        return response()->json([
            'success' => true,
            'unread_count' => $query->count(),
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::where('id', $id);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $notification = $query->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::where('read', false);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            });
        }

        $query->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::where('id', $id);
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $notification = $query->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    /**
     * Clear all notifications.
     */
    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor']);

        $query = Notification::query();
        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            });
        }

        $query->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications cleared successfully.',
        ]);
    }
}
