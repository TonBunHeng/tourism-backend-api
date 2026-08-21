<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\SecurityAlert;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecurityAlertController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of security alerts for administrators.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SecurityAlert::query()->orderBy('created_at', 'desc');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        if ($request->filled('type') && $request->type !== 'All') {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $totalCount = SecurityAlert::count();
        $unreadCount = SecurityAlert::where('is_read', false)->count();
        $highRiskCount = SecurityAlert::where('attempts', '>=', 6)->count();
        $totalLoginAttempts = LoginAttempt::count();
        $failedAttempts = LoginAttempt::where('success', false)->count();
        $blockedIpsCount = BlockedIp::where('is_active', true)->count();

        $alerts = $query->paginate($request->input('per_page', 50));

        // Get blocked IPs map
        $blockedIps = BlockedIp::where('is_active', true)->pluck('ip_address')->toArray();
        $blockedMap = array_flip($blockedIps);

        $items = collect($alerts->items())->map(function ($alert) use ($blockedMap) {
            $alertArray = $alert->toArray();
            $alertArray['is_ip_blocked'] = isset($blockedMap[$alert->ip_address]);
            return $alertArray;
        });

        return response()->json([
            'success' => true,
            'message' => 'Security alerts retrieved successfully.',
            'data' => $items,
            'pagination' => [
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
                'per_page' => $alerts->perPage(),
                'total' => $alerts->total(),
            ],
            'meta' => [
                'total_alerts' => $totalCount,
                'unread_count' => $unreadCount,
                'high_risk_count' => $highRiskCount,
                'total_login_attempts' => $totalLoginAttempts,
                'failed_attempts' => $failedAttempts,
                'blocked_ips_count' => $blockedIpsCount,
            ]
        ]);
    }

    /**
     * Mark a security alert as read.
     */
    public function markAsRead($id): JsonResponse
    {
        $alert = SecurityAlert::find($id);

        if (!$alert) {
            return $this->errorResponse('Security alert not found.', 404);
        }

        $alert->markAsRead();

        return $this->successResponse($alert, 'Security alert marked as read.');
    }

    /**
     * Mark all security alerts as read.
     */
    public function markAllRead(): JsonResponse
    {
        SecurityAlert::where('is_read', false)->update(['is_read' => true]);

        return $this->successResponse(null, 'All security alerts marked as read.');
    }

    /**
     * Delete a single security alert.
     */
    public function destroy($id): JsonResponse
    {
        $alert = SecurityAlert::find($id);

        if (!$alert) {
            return $this->errorResponse('Security alert not found.', 404);
        }

        $alert->delete();

        return $this->successResponse(null, 'Security alert deleted successfully.');
    }

    /**
     * Clear all security alerts.
     */
    public function clearAll(): JsonResponse
    {
        SecurityAlert::truncate();

        return $this->successResponse(null, 'All security alerts cleared successfully.');
    }

    /**
     * Block an IP Address.
     */
    public function blockIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $ip = trim($request->ip_address);
        $reason = $request->input('reason', 'Blocked due to brute-force threshold attack violation.');
        $adminId = $request->user() ? $request->user()->id : null;

        $blocked = BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_by' => $adminId,
                'is_active' => true,
                'blocked_at' => now(),
            ]
        );

        return $this->successResponse($blocked, "IP address {$ip} has been blocked successfully.");
    }

    /**
     * Unblock an IP Address.
     */
    public function unblockIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|string',
        ]);

        $ip = trim($request->ip_address);
        BlockedIp::where('ip_address', $ip)->delete();

        return $this->successResponse(null, "IP address {$ip} has been unblocked.");
    }

    /**
     * List all currently blocked IPs.
     */
    public function blockedIps(): JsonResponse
    {
        $blocked = BlockedIp::orderBy('blocked_at', 'desc')->get();

        return $this->successResponse($blocked, 'Blocked IPs retrieved successfully.');
    }

    /**
     * Get login attempts history / audit logs.
     */
    public function loginAttempts(Request $request): JsonResponse
    {
        $query = LoginAttempt::query()->orderBy('attempted_at', 'desc');

        if ($request->filled('email')) {
            $query->where('email', 'like', "%{$request->email}%");
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'like', "%{$request->ip_address}%");
        }

        if ($request->has('success')) {
            $query->where('success', $request->boolean('success'));
        }

        $attempts = $query->paginate($request->input('per_page', 25));

        return $this->successResponse($attempts, 'Login attempts retrieved successfully.');
    }

    /**
     * Export all security incident data for PDF / report export.
     */
    public function exportData(): JsonResponse
    {
        $alerts = SecurityAlert::orderBy('created_at', 'desc')->get();
        $blockedIps = BlockedIp::where('is_active', true)->get();
        $recentAttempts = LoginAttempt::orderBy('attempted_at', 'desc')->take(50)->get();

        return $this->successResponse([
            'generated_at' => now()->toIso8601String(),
            'total_alerts' => $alerts->count(),
            'unread_alerts' => $alerts->where('is_read', false)->count(),
            'high_risk_alerts' => $alerts->where('attempts', '>=', 6)->count(),
            'blocked_ips_count' => $blockedIps->count(),
            'alerts' => $alerts,
            'blocked_ips' => $blockedIps,
            'recent_attempts' => $recentAttempts,
        ], 'Security export data generated.');
    }
}
