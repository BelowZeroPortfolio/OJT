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

        return view('student.dashboard', [
            'attendanceRecords' => $attendanceRecords,
            'statistics' => $statistics,
        ]);
    }
}
