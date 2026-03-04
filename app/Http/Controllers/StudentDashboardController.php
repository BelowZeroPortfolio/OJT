<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StudentDashboardController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display the student dashboard with attendance records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Query attendance records for authenticated student only
        $attendanceRecords = $user->attendanceRecords()
            ->with('location')
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        // Cache dashboard statistics with 5-minute TTL
        $statistics = Cache::remember(
            'dashboard_stats_student_' . $user->id,
            CacheService::DASHBOARD_STATS_TTL,
            function () use ($user) {
                $totalHours = $user->attendanceRecords()
                    ->whereNotNull('total_hours')
                    ->sum('total_hours');

                $daysPresent = $user->attendanceRecords()
                    ->where('scan_type', 'time_in')
                    ->distinct('date')
                    ->count('date');

                return [
                    'total_hours' => round($totalHours, 2),
                    'days_present' => $daysPresent,
                ];
            }
        );

        // Get current attendance status for today
        $today = now('Asia/Manila')->toDateString();
        $todayRecord = $user->attendanceRecords()
            ->where('date', $today)
            ->first();

        $currentStatus = 'Not checked in today';
        if ($todayRecord) {
            if ($todayRecord->time_out) {
                $currentStatus = 'Completed - Checked out at ' . $todayRecord->time_out->setTimezone('Asia/Manila')->format('h:i A') . ' PHT';
            } else {
                $currentStatus = 'Checked in at ' . $todayRecord->time_in->setTimezone('Asia/Manila')->format('h:i A') . ' PHT';
            }
        }

        return view('student.dashboard', [
            'attendanceRecords' => $attendanceRecords,
            'statistics' => $statistics,
            'currentStatus' => $currentStatus,
        ]);
    }

    /**
     * Check for updates in student attendance data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdates(Request $request)
    {
        $user = Auth::user();
        
        // Get fresh statistics (not cached)
        $totalHours = $user->attendanceRecords()
            ->whereNotNull('total_hours')
            ->sum('total_hours');

        $daysPresent = $user->attendanceRecords()
            ->where('scan_type', 'time_in')
            ->distinct('date')
            ->count('date');

        $newStatistics = [
            'total_hours' => round($totalHours, 2),
            'days_present' => $daysPresent,
        ];

        // Get current cached statistics
        $cachedStats = Cache::get('dashboard_stats_student_' . $user->id, [
            'total_hours' => 0,
            'days_present' => 0,
        ]);

        // Check if there are updates
        $hasUpdates = ($newStatistics['total_hours'] != $cachedStats['total_hours']) || 
                     ($newStatistics['days_present'] != $cachedStats['days_present']);

        // Get current status
        $today = now('Asia/Manila')->toDateString();
        $todayRecord = $user->attendanceRecords()
            ->where('date', $today)
            ->first();

        $currentStatus = 'Not checked in today';
        if ($todayRecord) {
            if ($todayRecord->time_out) {
                $currentStatus = 'Completed - Checked out at ' . $todayRecord->time_out->setTimezone('Asia/Manila')->format('h:i A') . ' PHT';
            } else {
                $currentStatus = 'Checked in at ' . $todayRecord->time_in->setTimezone('Asia/Manila')->format('h:i A') . ' PHT';
            }
        }

        // Update cache if there are changes
        if ($hasUpdates) {
            Cache::put('dashboard_stats_student_' . $user->id, $newStatistics, CacheService::DASHBOARD_STATS_TTL);
        }

        return response()->json([
            'hasUpdates' => $hasUpdates,
            'statistics' => $newStatistics,
            'currentStatus' => $currentStatus,
            'timestamp' => now('Asia/Manila')->format('Y-m-d H:i:s T')
        ]);
    }
}
