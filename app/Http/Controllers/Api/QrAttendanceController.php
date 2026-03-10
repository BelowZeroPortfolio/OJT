<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;
use App\Services\AttendanceService;
use App\Services\ActivityLogService;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class QrAttendanceController extends Controller
{
    protected QrCodeService $qrCodeService;
    protected AttendanceService $attendanceService;
    protected ActivityLogService $activityLogService;

    public function __construct(
        QrCodeService $qrCodeService,
        AttendanceService $attendanceService,
        ActivityLogService $activityLogService
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->attendanceService = $attendanceService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Process QR code scan from student.
     */
    public function scan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'scan_type' => 'required|in:time_in,time_out',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $user = $request->user();

        // Validate token
        $tokenValidation = $this->qrCodeService->validateToken($data['token']);
        
        if (!$tokenValidation['valid']) {
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                "Invalid QR token: {$tokenValidation['message']}"
            );

            return response()->json([
                'success' => false,
                'message' => $tokenValidation['message'],
            ], 400);
        }

        $qrToken = $tokenValidation['token'];
        $location = $qrToken->location;

        // Validate student is assigned to this location
        if (!$this->attendanceService->validateLocationMatch($user, $location->id)) {
            $this->activityLogService->logInvalidScannerRequest(
                $request,
                "Location mismatch for user {$user->id} at location {$location->id}"
            );

            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this location',
            ], 403);
        }

        // Check for duplicate entry
        $date = Carbon::now()->toDateString();
        if ($this->attendanceService->checkDuplicateEntry($user->id, $location->id, $date, $data['scan_type'])) {
            return response()->json([
                'success' => false,
                'message' => 'You have already recorded ' . str_replace('_', ' ', $data['scan_type']) . ' for today',
            ], 409);
        }

        // Record attendance
        $attendanceData = [
            'user_id' => $user->id,
            'location_id' => $location->id,
            'timestamp' => Carbon::now('Asia/Manila'),
            'date' => $date,
            'scan_type' => $data['scan_type'],
            'scan_method' => 'qr_code',
            'qr_token_id' => $qrToken->id,
            'scanner_ip' => $request->ip(),
            'scan_latitude' => null,
            'scan_longitude' => null,
            'geofence_verified' => false,
            'is_valid' => true,
        ];

        if ($data['scan_type'] === 'time_in') {
            $attendanceData['time_in'] = Carbon::now();
        } else {
            // For time_out, find today's time_in record and update it
            $record = $this->attendanceService->getTodayRecord($user->id, $location->id);
            
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'No time in record found for today',
                ], 400);
            }

            $record->update([
                'time_out' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time out recorded successfully',
                'data' => [
                    'time_out' => $record->time_out,
                    'total_hours' => $record->total_hours,
                ],
            ], 200);
        }

        // Create time_in record
        $record = $this->attendanceService->createAttendanceRecord($attendanceData);

        return response()->json([
            'success' => true,
            'message' => 'Time in recorded successfully',
            'data' => [
                'time_in' => $record->time_in,
            ],
        ], 200);
    }
}
