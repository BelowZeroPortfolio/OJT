<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAuthenticationFailures
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
        try {
            $response = $next($request);

            // Log unauthorized access attempts (HTTP 401)
            if ($response->getStatusCode() === 401) {
                $this->activityLogService->logInvalidToken(
                    $request,
                    "Unauthorized access attempt to endpoint: {$request->path()}"
                );
            }

            return $response;
        } catch (AuthenticationException $e) {
            // Log authentication exception
            $this->activityLogService->logInvalidToken(
                $request,
                "Authentication exception: {$e->getMessage()}"
            );
            throw $e;
        }
    }
}
