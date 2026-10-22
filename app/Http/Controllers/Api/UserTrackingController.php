<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserTrackingController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of user tracking and activity audit logs.
     * Accessible by Administrators and Super Administrators.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::with(['user:id,name,email,avatar,role,status'])
            ->orderBy('created_at', 'desc');

        $trackedRoles = ['super_admin', 'admin', 'guide_editor', 'tourism_content_editor', 'business_owner'];

        // Filter by user role (Admin Staff, Content Editors, Business Owners only)
        if ($request->filled('role') && $request->role !== 'all') {
            $role = User::normalizeRole($request->role);
            if ($role === 'admin' || $role === 'super_admin') {
                $query->where(function ($q) {
                    $q->whereIn('user_role', ['admin', 'super_admin', 'Administrator', 'Super Admin'])
                      ->orWhereHas('user', function ($uq) {
                          $uq->whereIn('role', ['admin', 'super_admin']);
                      });
                });
            } else {
                $query->where(function ($q) use ($role) {
                    $q->where('user_role', $role)
                      ->orWhereHas('user', function ($uq) use ($role) {
                          $uq->where('role', $role);
                      });
                });
            }
        } else {
            $query->where(function ($q) use ($trackedRoles) {
                $q->whereIn('user_role', $trackedRoles)
                  ->orWhereHas('user', function ($uq) use ($trackedRoles) {
                      $uq->whereIn('role', $trackedRoles);
                  });
            });
        }

        // Filter by action category / keyword
        if ($request->filled('action') && $request->action !== 'all') {
            $actionFilter = $request->action;
            if ($actionFilter === 'create') {
                $query->where(fn($q) => $q->where('action', 'like', '%create%')->orWhere('action', 'like', '%store%'));
            } elseif ($actionFilter === 'update') {
                $query->where(fn($q) => $q->where('action', 'like', '%update%')->orWhere('action', 'like', '%edit%')->orWhere('action', 'like', '%status%'));
            } elseif ($actionFilter === 'delete') {
                $query->where(fn($q) => $q->where('action', 'like', '%delete%')->orWhere('action', 'like', '%destroy%')->orWhere('action', 'like', '%purge%'));
            } elseif ($actionFilter === 'approve') {
                $query->where(fn($q) => $q->where('action', 'like', '%approve%')->orWhere('action', 'like', '%verify%')->orWhere('action', 'like', '%activate%'));
            } elseif ($actionFilter === 'reject') {
                $query->where(fn($q) => $q->where('action', 'like', '%reject%')->orWhere('action', 'like', '%suspend%'));
            } elseif ($actionFilter === 'auth') {
                $query->where(fn($q) => $q->where('action', 'like', '%login%')->orWhere('action', 'like', '%logout%')->orWhere('action', 'like', '%auth%'));
            } else {
                $query->where('action', 'like', "%{$actionFilter}%");
            }
        }

        // Filter by target entity type
        if ($request->filled('entity_type') && $request->entity_type !== 'all') {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter by specific user ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by period (today, 7d, 30d)
        if ($request->filled('period')) {
            $period = $request->period;
            if ($period === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === '7d') {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            } elseif ($period === '30d') {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }
        }

        // Custom Date Range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Search text across user name, action, description, entity, and IP
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Calculate summary counters for tracked roles
        $adminActions = AuditLog::whereIn('user_role', ['admin', 'Administrator', 'super_admin', 'Super Admin'])
            ->orWhereHas('user', fn($uq) => $uq->whereIn('role', ['admin', 'super_admin']))
            ->count();
        $editorActions = AuditLog::whereIn('user_role', ['guide_editor', 'tourism_content_editor', 'Tourism Content Editor'])
            ->orWhereHas('user', fn($uq) => $uq->whereIn('role', ['guide_editor', 'tourism_content_editor']))
            ->count();
        $ownerActions = AuditLog::whereIn('user_role', ['business_owner', 'Business Owner'])
            ->orWhereHas('user', fn($uq) => $uq->where('role', 'business_owner'))
            ->count();
        $totalLogs = $adminActions + $editorActions + $ownerActions;
        $todayActions = AuditLog::whereDate('created_at', Carbon::today())
            ->where(function ($q) use ($trackedRoles) {
                $q->whereIn('user_role', $trackedRoles)
                  ->orWhereHas('user', fn($uq) => $uq->whereIn('role', $trackedRoles));
            })->count();

        $perPage = (int) $request->input('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'User tracking audit logs retrieved successfully.',
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'meta' => [
                'total' => $logs->total(),
                'last_page' => $logs->lastPage(),
                'current_page' => $logs->currentPage(),
                'per_page' => $logs->perPage(),
                'total_logs' => $totalLogs,
                'today_actions' => $todayActions,
                'admin_actions' => $adminActions,
                'guide_editor_actions' => $editorActions,
                'business_owner_actions' => $ownerActions,
            ],
        ]);
    }

    /**
     * Retrieve aggregate statistics for tracked user actions across roles.
     */
    public function statistics(Request $request): JsonResponse
    {
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();

        // Count actions by normalized roles
        $rolesCount = [
            'super_admin' => AuditLog::whereIn('user_role', ['super_admin', 'Super Admin'])->count(),
            'admin' => AuditLog::whereIn('user_role', ['admin', 'Administrator'])->count(),
            'guide_editor' => AuditLog::whereIn('user_role', ['guide_editor', 'tourism_content_editor', 'Tourism Content Editor'])->count(),
            'business_owner' => AuditLog::whereIn('user_role', ['business_owner', 'Business Owner'])->count(),
            'user' => AuditLog::whereIn('user_role', ['user', 'tourist', 'Tourist'])->count(),
        ];

        // Action categories
        $actionTypesCount = [
            'created' => AuditLog::where(fn($q) => $q->where('action', 'like', '%create%')->orWhere('action', 'like', '%store%'))->count(),
            'updated' => AuditLog::where(fn($q) => $q->where('action', 'like', '%update%')->orWhere('action', 'like', '%edit%')->orWhere('action', 'like', '%status%'))->count(),
            'approved' => AuditLog::where(fn($q) => $q->where('action', 'like', '%approve%')->orWhere('action', 'like', '%verify%'))->count(),
            'rejected' => AuditLog::where(fn($q) => $q->where('action', 'like', '%reject%')->orWhere('action', 'like', '%suspend%'))->count(),
            'deleted' => AuditLog::where(fn($q) => $q->where('action', 'like', '%delete%')->orWhere('action', 'like', '%destroy%'))->count(),
            'auth' => AuditLog::where(fn($q) => $q->where('action', 'like', '%login%')->orWhere('action', 'like', '%logout%'))->count(),
        ];

        // Top 5 most active actors
        $topActors = AuditLog::select('user_id', 'user_name', 'user_role', DB::raw('count(*) as action_count'))
            ->whereNotNull('user_name')
            ->groupBy('user_id', 'user_name', 'user_role')
            ->orderBy('action_count', 'desc')
            ->limit(5)
            ->get();

        // 7-day activity timeline
        $timeline = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = AuditLog::whereDate('created_at', $date)->count();
            $timeline[] = [
                'date' => $date,
                'formatted_date' => Carbon::parse($date)->format('M d'),
                'count' => $count,
            ];
        }

        return $this->successResponse([
            'total_actions' => AuditLog::count(),
            'today_actions' => AuditLog::whereDate('created_at', $today)->count(),
            'roles_breakdown' => $rolesCount,
            'action_types' => $actionTypesCount,
            'top_actors' => $topActors,
            'activity_timeline' => $timeline,
        ], 'User tracking statistics retrieved successfully.');
    }

    /**
     * Display the specified user tracking log detail.
     */
    public function show(string $id): JsonResponse
    {
        $log = AuditLog::with(['user:id,name,email,avatar,role,status'])->find($id);

        if (!$log) {
            return $this->errorResponse('Tracking audit log not found.', 404);
        }

        return $this->successResponse($log, 'Tracking audit log details retrieved.');
    }

    /**
     * Create a manual audit log record (Super Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|max:100',
            'entity_type' => 'nullable|string|max:100',
            'entity_id' => 'nullable|integer',
            'description' => 'required|string|max:255',
            'user_id' => 'nullable|integer|exists:users,id',
            'user_name' => 'nullable|string|max:100',
            'user_role' => 'nullable|string|max:50',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
        ]);

        if (!empty($validated['user_id'])) {
            $targetUser = User::find($validated['user_id']);
            if ($targetUser) {
                $validated['user_name'] = $validated['user_name'] ?? $targetUser->name;
                $validated['user_role'] = $validated['user_role'] ?? $targetUser->role;
            }
        } else {
            $currentUser = $request->user();
            $validated['user_id'] = $currentUser ? $currentUser->id : null;
            $validated['user_name'] = $validated['user_name'] ?? ($currentUser ? $currentUser->name : 'Super Admin');
            $validated['user_role'] = $validated['user_role'] ?? ($currentUser ? $currentUser->role : 'super_admin');
        }

        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        $log = AuditLog::create($validated);
        $log->load('user:id,name,email,avatar,role,status');

        return $this->successResponse($log, 'Tracking audit entry created successfully.', 201);
    }

    /**
     * Update an audit log record notes/description (Super Admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $log = AuditLog::find($id);

        if (!$log) {
            return $this->errorResponse('Tracking audit log not found.', 404);
        }

        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'action' => 'sometimes|required|string|max:100',
            'entity_type' => 'nullable|string|max:100',
            'entity_id' => 'nullable|integer',
            'old_values' => 'nullable|array',
            'new_values' => 'nullable|array',
        ]);

        $log->update($validated);
        $log->load('user:id,name,email,avatar,role,status');

        return $this->successResponse($log, 'Tracking audit entry updated successfully.');
    }

    /**
     * Delete a single audit log record (Super Admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $log = AuditLog::find($id);

        if (!$log) {
            return $this->errorResponse('Tracking audit log not found.', 404);
        }

        $log->delete();

        return $this->successResponse(null, 'Tracking audit entry deleted permanently.');
    }

    /**
     * Bulk clear tracking records (Super Admin only).
     */
    public function clearAll(Request $request): JsonResponse
    {
        $query = AuditLog::query();

        // Optional role filter for purging
        if ($request->filled('role') && $request->role !== 'all') {
            $role = User::normalizeRole($request->role);
            $query->where('user_role', $role);
        }

        // Optional age filter
        if ($request->filled('older_than_days')) {
            $days = (int) $request->older_than_days;
            $cutoff = Carbon::now()->subDays($days);
            $query->where('created_at', '<', $cutoff);
        }

        $deletedCount = $query->delete();

        return $this->successResponse([
            'deleted_count' => $deletedCount,
        ], "Successfully cleared {$deletedCount} tracking audit records.");
    }

    /**
     * Export tracked activity records for reporting (PDF and Excel).
     */
    public function exportData(Request $request): JsonResponse
    {
        $query = AuditLog::with('user:id,name,email,role')
            ->orderBy('created_at', 'desc')
            ->limit(1000);

        $trackedRoles = ['super_admin', 'admin', 'guide_editor', 'tourism_content_editor', 'business_owner'];

        if ($request->filled('role') && $request->role !== 'all') {
            $role = User::normalizeRole($request->role);
            if ($role === 'admin' || $role === 'super_admin') {
                $query->where(function ($q) {
                    $q->whereIn('user_role', ['admin', 'super_admin', 'Administrator', 'Super Admin'])
                      ->orWhereHas('user', fn($uq) => $uq->whereIn('role', ['admin', 'super_admin']));
                });
            } else {
                $query->where(function ($q) use ($role) {
                    $q->where('user_role', $role)
                      ->orWhereHas('user', function ($uq) use ($role) {
                          $uq->where('role', $role);
                      });
                });
            }
        } else {
            $query->where(function ($q) use ($trackedRoles) {
                $q->whereIn('user_role', $trackedRoles)
                  ->orWhereHas('user', fn($uq) => $uq->whereIn('role', $trackedRoles));
            });
        }

        if ($request->filled('action') && $request->action !== 'all') {
            $actionFilter = $request->action;
            if ($actionFilter === 'create') {
                $query->where(fn($q) => $q->where('action', 'like', '%create%')->orWhere('action', 'like', '%store%')->orWhere('action', 'like', '%add%'));
            } elseif ($actionFilter === 'update') {
                $query->where(fn($q) => $q->where('action', 'like', '%update%')->orWhere('action', 'like', '%edit%')->orWhere('action', 'like', '%modify%'));
            } elseif ($actionFilter === 'delete') {
                $query->where(fn($q) => $q->where('action', 'like', '%delete%')->orWhere('action', 'like', '%destroy%')->orWhere('action', 'like', '%remove%'));
            } elseif ($actionFilter === 'approve') {
                $query->where(fn($q) => $q->where('action', 'like', '%approve%')->orWhere('action', 'like', '%publish%')->orWhere('action', 'like', '%verify%'));
            } elseif ($actionFilter === 'reject') {
                $query->where(fn($q) => $q->where('action', 'like', '%reject%')->orWhere('action', 'like', '%suspend%')->orWhere('action', 'like', '%ban%'));
            } elseif ($actionFilter === 'auth') {
                $query->where(fn($q) => $q->where('action', 'like', '%login%')->orWhere('action', 'like', '%logout%')->orWhere('action', 'like', '%auth%'));
            } else {
                $query->where('action', 'like', "%{$actionFilter}%");
            }
        }

        // Filter by period (today, 7d, 30d)
        if ($request->filled('period')) {
            $period = $request->period;
            if ($period === 'today') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($period === '7d') {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            } elseif ($period === '30d') {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%");
            });
        }

        $records = $query->get()->map(function ($log) {
            return [
                'ID' => $log->id,
                'User' => $log->user_name ?? ($log->user ? $log->user->name : 'N/A'),
                'Role' => $log->user_role ?? ($log->user ? $log->user->role : 'N/A'),
                'Action' => $log->action,
                'Entity' => $log->entity_type . ($log->entity_id ? " #{$log->entity_id}" : ''),
                'Description' => $log->description,
                'IP Address' => $log->ip_address,
                'Timestamp' => $log->created_at ? $log->created_at->toIso8601String() : '',
            ];
        });

        return $this->successResponse($records, 'User tracking records exported successfully.');
    }
}
