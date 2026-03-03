<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
}
