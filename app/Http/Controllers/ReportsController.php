<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display the reports page with attendance data and advanced filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Get filters from request with smart defaults
            $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
            $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
            $course = $request->input('course');
            $locationId = $request->input('location_id');
            $studentName = $request->input('student_name');
            $sortBy = $request->input('sort_by', 'name');
            $sortOrder = $request->input('sort_order', 'asc');

        // Build base query for students with filters
        $studentsQuery = User::where('role', 'student');
        
        if ($course) {
            $studentsQuery->where('course', $course);
        }
        
        if ($locationId) {
            $studentsQuery->where('assigned_location_id', $locationId);
        }
        
        if ($studentName) {
            $studentsQuery->where('name', 'like', '%' . $studentName . '%');
        }

        // Get filtered students count
        $totalStudents = $studentsQuery->count();

        // Build attendance query with filters
        $attendanceQuery = AttendanceRecord::whereBetween('date', [$dateFrom, $dateTo]);
        
        if ($course) {
            $attendanceQuery->whereHas('user', function ($q) use ($course) {
                $q->where('course', $course);
            });
        }
        
        if ($locationId) {
            $attendanceQuery->where('location_id', $locationId);
        }
        
        if ($studentName) {
            $attendanceQuery->whereHas('user', function ($q) use ($studentName) {
                $q->where('name', 'like', '%' . $studentName . '%');
            });
        }

        // Total attendance records in date range
        $totalAttendance = $attendanceQuery->count();

        // Total hours
        $totalHours = (clone $attendanceQuery)
            ->whereNotNull('total_hours')
            ->sum('total_hours');

        // Calculate average attendance rate
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $workingDays = 0;
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }
        
        $expectedAttendance = $totalStudents * max($workingDays, 1);
        $avgRate = $expectedAttendance > 0 ? round(($totalAttendance / $expectedAttendance) * 100, 1) : 0;

        // Attendance by student with filters - OPTIMIZED
        $studentAttendance = DB::table('users')
            ->leftJoin('locations', 'users.assigned_location_id', '=', 'locations.id')
            ->leftJoin('attendance_records', function ($join) use ($dateFrom, $dateTo, $locationId) {
                $join->on('users.id', '=', 'attendance_records.user_id')
                    ->whereBetween('attendance_records.date', [$dateFrom, $dateTo]);
                if ($locationId) {
                    $join->where('attendance_records.location_id', $locationId);
                }
            })
            ->where('users.role', 'student')
            ->when($course, function ($q) use ($course) {
                $q->where('users.course', $course);
            })
            ->when($locationId, function ($q) use ($locationId) {
                $q->where('users.assigned_location_id', $locationId);
            })
            ->when($studentName, function ($q) use ($studentName) {
                $q->where('users.name', 'like', '%' . $studentName . '%');
            })
            ->select(
                'users.name',
                'users.student_id',
                'users.course',
                'locations.name as location',
                DB::raw('COUNT(DISTINCT attendance_records.date) as days_present'),
                DB::raw('COALESCE(SUM(attendance_records.total_hours), 0) as total_hours')
            )
            ->groupBy('users.id', 'users.name', 'users.student_id', 'users.course', 'locations.name')
            ->get()
            ->map(function ($student) use ($workingDays) {
                $attendanceRate = $workingDays > 0 
                    ? round(($student->days_present / $workingDays) * 100, 1) 
                    : 0;
                
                return [
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'course' => $student->course,
                    'location' => $student->location ?? 'N/A',
                    'days_present' => (int) $student->days_present,
                    'total_hours' => round($student->total_hours, 2),
                    'attendance_rate' => $attendanceRate,
                ];
            })
            ->sortBy($sortBy, SORT_REGULAR, $sortOrder === 'desc')
            ->values();

        // Attendance by location with filters - OPTIMIZED
        $locationAttendance = DB::table('locations')
            ->leftJoin('users', function ($join) use ($course) {
                $join->on('locations.id', '=', 'users.assigned_location_id')
                    ->where('users.role', 'student');
                if ($course) {
                    $join->where('users.course', $course);
                }
            })
            ->leftJoin('attendance_records', function ($join) use ($dateFrom, $dateTo, $course, $studentName) {
                $join->on('locations.id', '=', 'attendance_records.location_id')
                    ->whereBetween('attendance_records.date', [$dateFrom, $dateTo]);
            })
            ->when($locationId, function ($q) use ($locationId) {
                $q->where('locations.id', $locationId);
            })
            ->select(
                'locations.name',
                DB::raw('COUNT(DISTINCT users.id) as total_students'),
                DB::raw('COUNT(attendance_records.id) as total_attendance'),
                DB::raw('COALESCE(AVG(attendance_records.total_hours), 0) as avg_hours')
            )
            ->groupBy('locations.id', 'locations.name')
            ->get()
            ->map(function ($location) {
                return [
                    'name' => $location->name,
                    'total_students' => (int) $location->total_students,
                    'total_attendance' => (int) $location->total_attendance,
                    'avg_hours' => round($location->avg_hours, 2),
                ];
            });

        // Get filter options
        $courses = User::where('role', 'student')
            ->whereNotNull('course')
            ->distinct()
            ->pluck('course')
            ->sort()
            ->values();

        $locations = Location::active()
            ->orderBy('name')
            ->get(['id', 'name', 'location_code']);

            return view('admin.reports', [
                'totalStudents' => $totalStudents,
                'totalAttendance' => $totalAttendance,
                'avgRate' => $avgRate,
                'totalHours' => round($totalHours, 2),
                'studentAttendance' => $studentAttendance,
                'locationAttendance' => $locationAttendance,
                'courses' => $courses,
                'locations' => $locations,
                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'course' => $course,
                    'location_id' => $locationId,
                    'student_name' => $studentName,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Reports page error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'filters' => $request->all()
            ]);
            
            // Return a simplified view with error message
            return view('admin.reports', [
                'totalStudents' => 0,
                'totalAttendance' => 0,
                'avgRate' => 0,
                'totalHours' => 0,
                'studentAttendance' => collect([]),
                'locationAttendance' => collect([]),
                'courses' => collect([]),
                'locations' => Location::active()->get(['id', 'name', 'location_code']),
                'filters' => [
                    'date_from' => Carbon::now()->startOfMonth()->toDateString(),
                    'date_to' => Carbon::now()->endOfMonth()->toDateString(),
                    'course' => null,
                    'location_id' => null,
                    'student_name' => null,
                    'sort_by' => 'name',
                    'sort_order' => 'asc',
                ],
                'error' => 'A database error occurred. Please try again or contact support if the problem persists.',
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ]);
        }
    }
}
