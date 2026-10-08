<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userRole = $request->user()->role;
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Forbidden. Required roles: ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}