<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SupervisorDashboardController extends Controller
{
    /**
     * Display the supervisor dashboard.
     */
    public function index(Request $request)
    {
        $supervisor = Auth::user();
        
        // Get the location supervised by this user
        $location = $supervisor->supervisedLocation;
        
        if (!$location) {
            return view('supervisor.no-location');
        }
        
        // Get students assigned to this location
        $students = $location->students()
            ->with(['attendanceRecords' => function ($query) use ($request) {
                $query->whereDate('time_in', Carbon::today())
                      ->orderBy('time_in', 'desc');
            }])
            ->orderBy('name')
            ->get();
        
        // Get today's attendance statistics
        $today = Carbon::today();
        $totalStudents = $students->count();
        $presentToday = AttendanceRecord::whereDate('time_in', $today)
            ->whereIn('user_id', $students->pluck('id'))
            ->distinct('user_id')
            ->count('user_id');
        
        // Get recent attendance records for this location
        $recentAttendance = AttendanceRecord::with('user')
            ->whereIn('user_id', $students->pluck('id'))
            ->orderBy('time_in', 'desc')
            ->limit(10)
            ->get();
        
        return view('supervisor.dashboard', compact(
            'supervisor',
            'location',
            'students',
            'totalStudents',
            'presentToday',
            'recentAttendance'
        ));
    }
    
    /**
     * Check for updates (for real-time polling).
     */
    public function checkUpdates(Request $request)
    {
        $supervisor = Auth::user();
        $location = $supervisor->supervisedLocation;
        
        if (!$location) {
            return response()->json(['error' => 'No location assigned'], 404);
        }
        
        $students = $location->students()->pluck('id');
        
        // Get today's attendance count
        $presentToday = AttendanceRecord::whereDate('time_in', Carbon::today())
            ->whereIn('user_id', $students)
            ->distinct('user_id')
            ->count('user_id');
        
        // Get latest attendance record
        $latestAttendance = AttendanceRecord::with('user')
            ->whereIn('user_id', $students)
            ->orderBy('time_in', 'desc')
            ->first();
        
        return response()->json([
            'present_today' => $presentToday,
            'latest_attendance' => $latestAttendance ? [
                'student_name' => $latestAttendance->user->name,
                'check_in_time' => $latestAttendance->time_in->format('h:i A'),
                'method' => $latestAttendance->scan_method,
            ] : null,
        ]);
    }
}
