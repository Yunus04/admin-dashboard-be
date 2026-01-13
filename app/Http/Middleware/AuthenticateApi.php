<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use sanctum guard for API token authentication
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Check if token has expired
        $token = $user->currentAccessToken();
        if ($token && $token->expires_at && $token->expires_at->isPast()) {
            // Delete expired token - don't return error, let frontend handle it via refresh
            // The frontend has interceptor for 401 that will try to refresh token
            $token->delete();
        }

        // Set the authenticated user on the request so controllers can access it via $request->user()
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
