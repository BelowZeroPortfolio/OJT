<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceController extends Controller
{
    /**
     * Get attendance records for the authenticated student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Implement data isolation - student can only see own records
        if (!$user->isStudent()) {
            return response()->json([
                'error' => 'Unauthorized access'
            ], 403);
        }

        // Cache key unique to this student
        $cacheKey = "student_attendance_{$user->id}";

        // Add caching with 5-minute TTL
        $data = Cache::remember($cacheKey, 300, function () use ($user) {
            // Query attendance records for authenticated student only
            $attendanceRecords = $user->attendanceRecords()
                ->with('location')
                ->orderBy('date', 'desc')
                ->orderBy('time_in', 'desc')
                ->limit(20)
                ->get();

            // Calculate total hours and attendance statistics
            $totalHours = $user->attendanceRecords()
                ->whereNotNull('total_hours')
                ->sum('total_hours');

            $daysPresent = $user->attendanceRecords()
                ->where('scan_type', 'time_in')
                ->distinct('date')
                ->count('date');

            return [
                'records' => $attendanceRecords->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date->format('Y-m-d'),
                        'time_in' => $record->time_in->format('Y-m-d H:i:s'),
                        'time_out' => $record->time_out ? $record->time_out->format('Y-m-d H:i:s') : null,
                        'location' => [
                            'id' => $record->location->id,
                            'name' => $record->location->name,
                        ],
                        'total_hours' => $record->total_hours,
                    ];
                }),
                'statistics' => [
                    'total_hours' => round($totalHours, 2),
                    'days_present' => $daysPresent,
                ],
            ];
        });

        return response()->json($data);
    }

    /**
     * Process RFID attendance scan for student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rfidAttendance(Request $request)
    {
        try {
            $request->validate([
                'rfid_number' => 'required|string|max:255'
            ]);

            $rfidNumber = trim($request->rfid_number);
            
            // Find user by RFID number
            $user = User::where('rfid_number', $rfidNumber)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'RFID number not found. Please contact your administrator.'
                ], 404);
            }

            if (!$user->isStudent()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This RFID is not registered for a student account.'
                ], 403);
            }

            // Check if the authenticated user matches the RFID user (for security)
            $authUser = auth()->user();
            if ($authUser->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This RFID card is not registered to your account.'
                ], 403);
            }

            // Check if user has an assigned location
            if (!$user->assigned_location_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No location assigned. Please contact your administrator.'
                ], 400);
            }

            $today = Carbon::today('Asia/Manila');
            $now = Carbon::now('Asia/Manila');

            // Check for existing attendance record today
            $existingRecord = AttendanceRecord::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            DB::beginTransaction();

            if (!$existingRecord) {
                // Time In - Create new record
                try {
                    $record = AttendanceRecord::create([
                        'user_id' => $user->id,
                        'location_id' => $user->assigned_location_id,
                        'date' => $today,
                        'time_in' => $now,
                        'scan_type' => 'time_in',
                        'rfid_number' => $rfidNumber,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle unique constraint violation
                    if ($e->getCode() === '23000') {
                        DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Attendance record already exists for today. Please try again.'
                        ], 400);
                    }
                    throw $e;
                }

                DB::commit();

                // Clear cache
                Cache::forget("student_attendance_{$user->id}");

                return response()->json([
                    'success' => true,
                    'message' => "Welcome {$user->name}! Time in recorded at {$now->format('h:i A')}",
                    'status' => 'Checked in at ' . $now->format('h:i A'),
                    'action' => 'time_in',
                    'record' => [
                        'date' => $record->date->format('M d, Y'),
                        'time_in' => $record->time_in->format('h:i A'),
                        'location' => $user->location->name
                    ]
                ]);

            } elseif ($existingRecord && !$existingRecord->time_out) {
                // Time Out - Update existing record
                $timeIn = Carbon::parse($existingRecord->time_in);
                $totalHours = $now->diffInMinutes($timeIn) / 60;

                $existingRecord->update([
                    'time_out' => $now,
                    'total_hours' => round($totalHours, 2),
                    'scan_type' => 'time_out'
                ]);

                DB::commit();

                // Clear cache
                Cache::forget("student_attendance_{$user->id}");

                return response()->json([
                    'success' => true,
                    'message' => "Goodbye {$user->name}! Time out recorded at {$now->format('h:i A')}. Total hours: " . round($totalHours, 2),
                    'status' => 'Checked out at ' . $now->format('h:i A'),
                    'action' => 'time_out',
                    'record' => [
                        'date' => $existingRecord->date->format('M d, Y'),
                        'time_in' => $existingRecord->time_in->format('h:i A'),
                        'time_out' => $now->format('h:i A'),
                        'total_hours' => round($totalHours, 2),
                        'location' => $user->location->name
                    ]
                ]);

            } else {
                // Already completed for today
                DB::rollback();
                
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed your attendance for today.',
                    'status' => 'Completed for today'
                ], 400);
            }

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Invalid RFID format.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('RFID Attendance Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'System error. Please try again or contact support.'
            ], 500);
        }
    }
}
