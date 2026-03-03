<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * Get all attendance records with filtering support.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Create cache key based on filters
        $cacheKey = 'admin_attendance_' . md5(json_encode($request->all()));

        // Cache for 5 minutes
        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            // Build query with eager loading
            $query = AttendanceRecord::with(['user', 'location'])
                ->orderBy('date', 'desc')
                ->orderBy('time_in', 'desc');

            // Apply filters
            if ($request->filled('student_name')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->student_name . '%');
                });
            }

            if ($request->filled('course')) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('course', $request->course);
                });
            }

            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            if ($request->filled('date_from')) {
                $query->where('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('date', '<=', $request->date_to);
            }

            // Get paginated results
            $attendanceRecords = $query->paginate(20);

            // Calculate statistics
            $totalStudents = User::where('role', 'student')->count();
            
            $presentToday = AttendanceRecord::whereDate('date', Carbon::today())
                ->where('scan_type', 'time_in')
                ->distinct('user_id')
                ->count('user_id');

            $absentToday = $totalStudents - $presentToday;

            return [
                'attendance_records' => $attendanceRecords->items(),
                'pagination' => [
                    'current_page' => $attendanceRecords->currentPage(),
                    'last_page' => $attendanceRecords->lastPage(),
                    'per_page' => $attendanceRecords->perPage(),
                    'total' => $attendanceRecords->total(),
                ],
                'statistics' => [
                    'total_students' => $totalStudents,
                    'present_today' => $presentToday,
                    'absent_today' => $absentToday,
                ],
                'has_new_records' => false, // This would be determined by comparing with previous data
            ];
        });

        return response()->json($data);
    }
}
