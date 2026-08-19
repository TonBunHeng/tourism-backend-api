<?php

namespace App\Http\Middleware;

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

        $allowedRoles = !empty($roles) ? $roles : ['Super Admin', 'Admin', 'Guide / Editor'];

        if (!in_array($user->role, $allowedRoles, true)) {
            return $this->errorResponse('Access denied. Administrator privileges required.', 403);
        }

        return $next($request);
    }
}
