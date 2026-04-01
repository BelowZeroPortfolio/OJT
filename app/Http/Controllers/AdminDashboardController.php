<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\Location;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display the admin dashboard with attendance records and filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Build query with eager loading and select only needed columns
            $query = AttendanceRecord::with([
                    'user:id,name,student_id,course',
                    'location:id,name,location_code'
                ])
                ->select('id', 'user_id', 'location_id', 'date', 'time_in', 'time_out', 'total_hours', 'scan_type')
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

            // Paginate results
            $attendanceRecords = $query->paginate(20)->withQueryString();

            // Cache dashboard statistics with 5-minute TTL
            $statistics = Cache::remember('dashboard_stats_admin', CacheService::DASHBOARD_STATS_TTL, function () {
                try {
                    $totalStudents = User::where('role', 'student')->count();
                    
                    // Use simple date string instead of Carbon timezone
                    $today = date('Y-m-d');
                    $presentToday = AttendanceRecord::where('date', $today)
                        ->where('scan_type', 'time_in')
                        ->distinct()
                        ->count('user_id');

                    $absentToday = max(0, $totalStudents - $presentToday);

                    return [
                        'total_students' => $totalStudents,
                        'present_today' => $presentToday,
                        'absent_today' => $absentToday,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Dashboard statistics error: ' . $e->getMessage());
                    return [
                        'total_students' => 0,
                        'present_today' => 0,
                        'absent_today' => 0,
                    ];
                }
            });

            // Cache location lookup data with 1-hour TTL
            $locations = $this->cacheService->getAllLocations();
            if (!$locations) {
                $locations = Location::active()->get();
                $this->cacheService->cacheAllLocations($locations);
            }

            $courses = User::where('role', 'student')
                ->whereNotNull('course')
                ->distinct()
                ->pluck('course');

            return view('admin.dashboard', [
                'attendanceRecords' => $attendanceRecords,
                'statistics' => $statistics,
                'locations' => $locations,
                'courses' => $courses,
                'filters' => $request->only(['student_name', 'course', 'location_id', 'date_from', 'date_to']),
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user' => $request->user()?->id
            ]);
            
            // Return view with empty data and error message
            return view('admin.dashboard', [
                'attendanceRecords' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'statistics' => [
                    'total_students' => 0,
                    'present_today' => 0,
                    'absent_today' => 0,
                ],
                'locations' => Location::active()->get(),
                'courses' => collect([]),
                'filters' => [],
                'error' => 'A database error occurred. Please try again or contact support if the problem persists.',
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}
