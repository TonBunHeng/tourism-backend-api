<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    use ApiResponse;

    /**
     * Handle an incoming request.
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

        $allowedRoles = !empty($roles)
            ? array_map([User::class, 'normalizeRole'], $roles)
            : [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN, User::ROLE_GUIDE_EDITOR];

        $userRole = User::normalizeRole($user->role);

        if (!in_array($userRole, $allowedRoles, true)) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        return $next($request);
    }
}
