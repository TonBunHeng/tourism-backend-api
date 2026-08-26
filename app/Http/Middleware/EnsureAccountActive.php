<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     * Ensure the authenticated user account is Active and not Inactive or Suspended.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'Active') {
            return $this->errorResponse('Your account is currently ' . strtolower($user->status) . '. Please contact support.', 403);
        }

        return $next($request);
    }
}
