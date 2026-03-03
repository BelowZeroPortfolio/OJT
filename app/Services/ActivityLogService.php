<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Log a rate limit violation
     *
     * @param Request $request
     * @param string|null $details
     * @return ActivityLog
     */
    public function logRateLimitViolation(Request $request, ?string $details = null): ActivityLog
    {
        return $this->createLog(
            'rate_limit',
            $request,
            $details ?? 'Rate limit exceeded'
        );
    }

    /**
     * Log an invalid token attempt
     *
     * @param Request $request
     * @param string|null $details
     * @return ActivityLog
     */
    public function logInvalidToken(Request $request, ?string $details = null): ActivityLog
    {
        return $this->createLog(
            'invalid_token',
            $request,
            $details ?? 'Invalid or expired authentication token'
        );
    }

    /**
     * Log a failed login attempt
     *
     * @param Request $request
     * @param string|null $email
     * @return ActivityLog
     */
    public function logFailedLogin(Request $request, ?string $email = null): ActivityLog
    {
        $details = 'Failed login attempt';
        if ($email) {
            $details .= " for email: {$email}";
        }

        return $this->createLog(
            'failed_login',
            $request,
            $details
        );
    }

    /**
     * Log invalid RFID scanner request
     *
     * @param Request $request
     * @param string|null $details
     * @return ActivityLog
     */
    public function logInvalidScannerRequest(Request $request, ?string $details = null): ActivityLog
    {
        return $this->createLog(
            'invalid_scanner_request',
            $request,
            $details ?? 'Invalid RFID scanner request'
        );
    }

    /**
     * Log unauthorized access attempt
     *
     * @param Request $request
     * @param string|null $details
     * @return ActivityLog
     */
    public function logUnauthorizedAccess(Request $request, ?string $details = null): ActivityLog
    {
        return $this->createLog(
            'unauthorized_access',
            $request,
            $details ?? 'Unauthorized access attempt'
        );
    }

    /**
     * Create an activity log entry
     *
     * @param string $eventType
     * @param Request $request
     * @param string $details
     * @return ActivityLog
     */
    protected function createLog(string $eventType, Request $request, string $details): ActivityLog
    {
        return ActivityLog::create([
            'event_type' => $eventType,
            'ip_address' => $request->ip(),
            'user_id' => $request->user()?->id,
            'request_details' => $details,
            'user_agent' => $request->userAgent(),
        ]);
    }
}
