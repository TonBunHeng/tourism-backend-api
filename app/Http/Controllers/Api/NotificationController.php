<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\UserNotificationSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

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
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

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
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

        $query = Notification::where('id', $id);
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            });
        }

        $notification = $query->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        $notification->markAsRead();

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

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data' => $notification,
            'meta' => [
                'total' => (clone $baseQuery)->count(),
                'unread_count' => (clone $baseQuery)->where('read', false)->count(),
            ],
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

        $query = Notification::where('read', false);
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            });
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

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
            'meta' => [
                'total' => (clone $baseQuery)->count(),
                'unread_count' => 0,
            ],
        ]);
    }

    /**
     * Subscribe a client device / browser endpoint to Web Push notifications.
     */
    public function subscribePush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'nullable|array',
            'keys.p256dh' => 'nullable|string',
            'keys.auth' => 'nullable|string',
            'content_encoding' => 'nullable|string',
        ]);

        $user = $request->user();
        $endpoint = $validated['endpoint'];
        $publicKey = $validated['keys']['p256dh'] ?? $request->input('publicKey');
        $authToken = $validated['keys']['auth'] ?? $request->input('authToken');
        $encoding = $validated['content_encoding'] ?? 'aesgcm';

        $subscription = PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user ? $user->id : null,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $encoding,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Push notification subscription registered successfully.',
            'data' => [
                'id' => $subscription->id,
                'endpoint' => $subscription->endpoint,
                'subscribed_at' => $subscription->updated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Unsubscribe a client device / browser endpoint.
     */
    public function unsubscribePush(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        $user = $request->user();

        if ($endpoint) {
            PushSubscription::where('endpoint', $endpoint)->delete();
        } elseif ($user) {
            PushSubscription::where('user_id', $user->id)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Push notification subscription removed successfully.',
        ]);
    }

    /**
     * Get user notification preferences/settings.
     */
    public function getSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $settings = UserNotificationSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'push_enabled' => true,
                'events_enabled' => true,
                'messages_enabled' => true,
                'system_enabled' => true,
                'promotions_enabled' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'push_enabled' => (bool) $settings->push_enabled,
                'events_enabled' => (bool) $settings->events_enabled,
                'messages_enabled' => (bool) $settings->messages_enabled,
                'system_enabled' => (bool) $settings->system_enabled,
                'promotions_enabled' => (bool) $settings->promotions_enabled,
            ],
        ]);
    }

    /**
     * Update user notification preferences/settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'push_enabled' => 'nullable|boolean',
            'events_enabled' => 'nullable|boolean',
            'messages_enabled' => 'nullable|boolean',
            'system_enabled' => 'nullable|boolean',
            'promotions_enabled' => 'nullable|boolean',
        ]);

        $settings = UserNotificationSetting::updateOrCreate(
            ['user_id' => $user->id],
            array_filter($validated, fn($val) => $val !== null)
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
            'data' => [
                'push_enabled' => (bool) $settings->push_enabled,
                'events_enabled' => (bool) $settings->events_enabled,
                'messages_enabled' => (bool) $settings->messages_enabled,
                'system_enabled' => (bool) $settings->system_enabled,
                'promotions_enabled' => (bool) $settings->promotions_enabled,
            ],
        ]);
    }

    /**
     * Return public VAPID key for web push subscription.
     */
    public function vapidPublicKey(): JsonResponse
    {
        $publicKey = env('VAPID_PUBLIC_KEY', 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkrxZJjSgSnfckjBJuBkr3qBUYIHBQFLXYp5NAlH8A=');

        return response()->json([
            'success' => true,
            'data' => [
                'public_key' => $publicKey,
            ],
        ]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

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

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
            'meta' => [
                'total' => (clone $baseQuery)->count(),
                'unread_count' => (clone $baseQuery)->where('read', false)->count(),
            ],
        ]);
    }

    /**
     * Clear all notifications.
     */
    public function clearAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $isAdmin = in_array($user->role ?? '', ['Super Admin', 'Admin', 'Guide / Editor'], true);

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
            'meta' => [
                'total' => 0,
                'unread_count' => 0,
            ],
        ]);
    }
}
