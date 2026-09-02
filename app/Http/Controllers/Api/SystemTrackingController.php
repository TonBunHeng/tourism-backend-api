<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\GalleryMedia;
use App\Models\Place;
use App\Models\Review;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemTrackingController extends Controller
{
    use ApiResponse;

    /**
     * Get live tracking overview for all roles in tourism-travel.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required for system tracking.', 403);
        }

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($role = $request->input('role')) {
            $normalizedRole = User::normalizeRole($role);
            $query->where('user_role', $normalizedRole);
        }

        if ($action = $request->input('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $activities = $query->paginate($perPage);

        // Role Breakdown Stats
        $roleBreakdown = [
            'business_owner' => [
                'role' => 'Business Owner',
                'total_users' => User::where('role', User::ROLE_BUSINESS_OWNER)->count(),
                'online_users' => User::where('role', User::ROLE_BUSINESS_OWNER)->where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'total_businesses' => Business::count(),
                'pending_verification' => Business::where('verification_status', 'pending')->count(),
                'approved_businesses' => Business::where('verification_status', 'approved')->count(),
            ],
            'guide_editor' => [
                'role' => 'Guide / Editor',
                'total_users' => User::where('role', User::ROLE_GUIDE_EDITOR)->count(),
                'online_users' => User::where('role', User::ROLE_GUIDE_EDITOR)->where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'total_places' => Place::count(),
                'pending_places' => Place::where('status', 'Pending')->count(),
                'active_places' => Place::where('status', 'Active')->count(),
            ],
            'tourist' => [
                'role' => 'Tourist (User)',
                'total_users' => User::where('role', User::ROLE_USER)->count(),
                'online_users' => User::where('role', User::ROLE_USER)->where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'total_reviews' => Review::count(),
                'total_galleries' => GalleryMedia::count(),
            ],
            'admin' => [
                'role' => 'Admin & Super Admin',
                'total_users' => User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->count(),
                'online_users' => User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])->where('last_active_at', '>=', now()->subMinutes(5))->count(),
            ],
        ];

        // Real Users List from DB
        $userQuery = User::query();
        if ($role = $request->input('role')) {
            $normalizedRole = User::normalizeRole($role);
            $userQuery->where('role', $normalizedRole);
        }
        if ($search = $request->input('search')) {
            $userQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        $realUsers = $userQuery->orderBy('last_active_at', 'desc')->limit(50)->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role' => $u->role,
                'status' => $u->status,
                'location' => $u->location,
                'avatar' => $u->avatar,
                'is_online' => $u->isOnline(),
                'last_active' => $u->last_active_human,
                'last_active_at' => $u->last_active_at ? $u->last_active_at->toIso8601String() : null,
                'created_at' => $u->created_at ? $u->created_at->toIso8601String() : null,
            ];
        });

        return $this->successResponse([
            'role_breakdown' => $roleBreakdown,
            'users' => $realUsers,
            'activities' => $activities->items(),
            'pagination' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
            'meta' => [
                'total_logs' => AuditLog::count(),
                'total_registered_users' => User::count(),
                'online_users_now' => User::where('last_active_at', '>=', now()->subMinutes(5))->count(),
                'pending_verifications' => Business::where('verification_status', 'pending')->count() + Place::where('status', 'Pending')->count(),
            ]
        ], 'System tracking telemetry retrieved successfully.');
    }

    /**
     * Get real-time live activity stream.
     */
    public function liveFeed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $logs = AuditLog::with('user')->orderBy('created_at', 'desc')->limit(30)->get();

        return $this->successResponse(
            $logs,
            'Live system activity feed retrieved successfully.'
        );
    }
}
