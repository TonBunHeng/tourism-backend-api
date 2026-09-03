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

            $user = Auth::user();
            $finalUserId = $userId ?? ($user ? $user->id : null);
            $userName = $user ? $user->name : ($finalUserId ? 'User #' . $finalUserId : 'System / Guest');
            $userRole = $user ? $user->role : 'Guest';

            return AuditLog::create([
                'user_id' => $finalUserId,
                'user_name' => $userName,
                'user_role' => $userRole,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore failures if audit_logs table or features are decommissioned
            return null;
        }
    }
}
