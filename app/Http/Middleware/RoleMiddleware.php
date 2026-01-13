<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login first.',
            ], 401);
        }

        // Parse roles - handle pipe character for multiple roles
        $parsedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, '|')) {
                $parsedRoles = array_merge($parsedRoles, explode('|', $role));
            } else {
                $parsedRoles[] = $role;
            }
        }

        $userRole = $request->user()->role;

        if (!in_array($userRole, $parsedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to access this resource.',
                'required_roles' => $parsedRoles,
                'user_role' => $userRole,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has any of the specified roles.
     */
    public static function hasAnyRole(Request $request, array $roles): bool
    {
        if (!$request->user()) {
            return false;
        }

        return in_array($request->user()->role, $roles);
    }

    /**
     * Check if user has all specified roles.
     */
    public static function hasAllRoles(Request $request, array $roles): bool
    {
        if (!$request->user()) {
            return false;
        }

        $userRole = $request->user()->role;
        return in_array($userRole, $roles) && count($roles) === 1;
    }
}

