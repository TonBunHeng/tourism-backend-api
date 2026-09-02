<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     * Check if authenticated user has any of the specified roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthenticated.', 401);
        }

        if (strtolower($user->status ?? '') !== 'active') {
            return $this->errorResponse('Account is ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        // Admin & Super Admin have full access across all role modules
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowedRoles = array_map([User::class, 'normalizeRole'], $roles);
        $userRole = User::normalizeRole($user->role);

        if (!in_array($userRole, $allowedRoles, true)) {
            return $this->errorResponse('Access denied. You do not have the required role permissions.', 403);
        }

        return $next($request);
    }
}
