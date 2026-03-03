<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user()) {
            // Log authentication required
            $this->activityLogService->logUnauthorizedAccess(
                $request,
                "Authentication required for role: {$role}"
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required',
                ], 401);
            }
            
            return redirect()->route('login');
        }

        if ($request->user()->role !== $role) {
            // Log unauthorized access attempt
            $this->activityLogService->logUnauthorizedAccess(
                $request,
                "User role '{$request->user()->role}' attempted to access '{$role}' endpoint: {$request->path()}"
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }
            
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}
