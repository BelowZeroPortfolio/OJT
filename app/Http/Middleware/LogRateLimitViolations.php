<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRateLimitViolations
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log rate limit violations (HTTP 429)
        if ($response->getStatusCode() === 429) {
            $this->activityLogService->logRateLimitViolation(
                $request,
                "Rate limit exceeded for endpoint: {$request->path()}"
            );
        }

        return $response;
    }
}
