<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of audit logs with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully.',
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'meta' => [
                'total_logs' => AuditLog::count(),
                'unique_actions' => AuditLog::distinct('action')->pluck('action'),
            ]
        ]);
    }

    /**
     * Display the specified audit log entry.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            return $this->errorResponse('Access denied.', 403);
        }

        $log = AuditLog::with('user')->find($id);

        if (!$log) {
            return $this->errorResponse('Audit log record not found.', 404);
        }

        return $this->successResponse(
            $log,
            'Audit log retrieved successfully.'
        );
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $fileName = 'angkorverses_audit_logs_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'User', 'Role', 'Action', 'Entity Type', 'Entity ID', 'Description', 'IP Address', 'Timestamp']);

            AuditLog::orderBy('created_at', 'desc')->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->user_name,
                        $log->user_role,
                        $log->action,
                        $log->entity_type,
                        $log->entity_id,
                        $log->description,
                        $log->ip_address,
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
