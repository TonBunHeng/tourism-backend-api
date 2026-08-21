<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if ($ip && BlockedIp::isBlocked($ip)) {
            return response()->json([
                'success' => false,
                'error' => 'IP_BLOCKED',
                'message' => "Access Denied: Your IP address ({$ip}) has been blocked by system administrators due to security violations.",
                'ip_blocked' => true,
                'ip' => $ip,
            ], 403);
        }

        return $next($request);
    }
}
