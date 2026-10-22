<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log an action to the audit_logs table (Safe / Decommissioned).
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): ?AuditLog {
        try {
            if (!class_exists(AuditLog::class)) {
                return null;
            }

            $user = request()->user() ?? auth('sanctum')->user() ?? Auth::user();
            $finalUserId = $userId ?? ($user ? $user->id : null);
            $userName = $user ? $user->name : ($finalUserId ? 'User #' . $finalUserId : 'System User');
            $userRole = $user ? ($user->role ?? 'admin') : 'admin';

            return AuditLog::create([
                'user_id' => $finalUserId,
                'user_name' => $userName,
                'user_role' => $userRole,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => substr($description ?? '', 0, 255),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore failures if audit_logs table or features are decommissioned
            return null;
        }
    }
}
