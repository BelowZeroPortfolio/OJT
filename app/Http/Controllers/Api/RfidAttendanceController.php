<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\ActivityLogService;
use App\Jobs\ProcessAttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RfidAttendanceController extends Controller
{
    protected AttendanceService $attendanceService;
    protected ActivityLogService $activityLogService;

    public function __construct(
        AttendanceService $attendanceService,
        ActivityLogService $activityLogService
    ) {
        $this->attendanceService = $attendanceService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Process RFID scan from scanner hardware.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        // Validate scanner token
        $scannerToken = $request->header('X-Scanner-Token');
        if (!$this->validateScannerToken($scannerToken)) {
            // Log invalid scanner token
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                'Invalid scanner token provided'
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid scanner token',
            ], 401);
        }

        // Validate incoming payload
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string|max:50',
            'location_id' => 'required|integer|exists:locations,id',
            'timestamp' => 'required|date',
            'scan_type' => 'required|in:time_in,time_out',
        ]);

        if ($validator->fails()) {
            // Log validation failure
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                'Validation failed: ' . json_encode($validator->errors())
            );

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['scanner_ip'] = $request->ip();

        // Validate student exists
        $student = $this->attendanceService->validateStudentExists($data['student_id']);
        if (!$student) {
            // Log student not found
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                "Student not found: {$data['student_id']}"
            );

            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        // Validate location match
        if (!$this->attendanceService->validateLocationMatch($student, $data['location_id'])) {
            // Log location mismatch
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                "Location mismatch for student {$data['student_id']} at location {$data['location_id']}"
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid location for student',
            ], 403);
        }

        // Check for duplicate entry
        $date = \Carbon\Carbon::parse($data['timestamp'])->toDateString();
        if ($this->attendanceService->checkDuplicateEntry($student->id, $data['location_id'], $date, $data['scan_type'])) {
            // Log duplicate entry attempt
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                "Duplicate attendance entry for student {$data['student_id']} on {$date}"
            );

            return response()->json([
                'success' => false,
                'message' => 'Duplicate attendance entry',
            ], 409);
        }

        // Dispatch job to process attendance asynchronously
        $data['user_id'] = $student->id;
        ProcessAttendanceLog::dispatch($data)->onQueue('high');

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded',
        ], 200);
    }

    /**
     * Validate scanner token.
     *
     * @param string|null $token
     * @return bool
     */
    protected function validateScannerToken(?string $token): bool
    {
        if (!$token) {
            return false;
        }

        // Get valid scanner tokens from config
        $validTokens = config('scanner.tokens', []);
        
        return in_array($token, $validTokens);
    }
}
