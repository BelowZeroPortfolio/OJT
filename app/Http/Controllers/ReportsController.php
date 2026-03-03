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

        // Attendance by student with filters
        $studentAttendance = $studentsQuery
            ->with([
                'attendanceRecords' => function ($query) use ($dateFrom, $dateTo, $locationId) {
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                        ->when($locationId, function ($q) use ($locationId) {
                            $q->where('location_id', $locationId);
                        })
                        ->select('user_id', 'date', 'total_hours');
                },
                'location'
            ])
            ->get()
            ->map(function ($student) use ($workingDays) {
                $records = $student->attendanceRecords;
                $daysPresent = $records->unique('date')->count();
                $totalHours = $records->whereNotNull('total_hours')->sum('total_hours');
                
                $attendanceRate = $workingDays > 0 
                    ? round(($daysPresent / $workingDays) * 100, 1) 
                    : 0;
                
                return [
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'course' => $student->course,
                    'location' => $student->location ? $student->location->name : 'N/A',
                    'days_present' => $daysPresent,
                    'total_hours' => round($totalHours, 2),
                    'attendance_rate' => $attendanceRate,
                ];
            })
            ->sortBy(function ($item) use ($sortBy) {
                return $item[$sortBy];
            }, SORT_REGULAR, $sortOrder === 'desc')
            ->values();

        // Attendance by location with filters
        $locationQuery = Location::query();
        
        if ($locationId) {
            $locationQuery->where('id', $locationId);
        }
        
        $locationAttendance = $locationQuery
            ->with([
                'users' => function ($query) use ($course) {
                    $query->where('role', 'student')
                        ->when($course, function ($q) use ($course) {
                            $q->where('course', $course);
                        });
                },
                'attendanceRecords' => function ($query) use ($dateFrom, $dateTo, $course, $studentName) {
                    $query->whereBetween('date', [$dateFrom, $dateTo])
                        ->when($course, function ($q) use ($course) {
                            $q->whereHas('user', function ($uq) use ($course) {
                                $uq->where('course', $course);
                            });
                        })
                        ->when($studentName, function ($q) use ($studentName) {
                            $q->whereHas('user', function ($uq) use ($studentName) {
                                $uq->where('name', 'like', '%' . $studentName . '%');
                            });
                        });
                }
            ])
            ->get()
            ->map(function ($location) {
                $records = $location->attendanceRecords;
                $avgHours = $records->whereNotNull('total_hours')->avg('total_hours');
                
                return [
                    'name' => $location->name,
                    'total_students' => $location->users->count(),
                    'total_attendance' => $records->count(),
                    'avg_hours' => round($avgHours ?? 0, 2),
                ];
            });

        // Get filter options
        $courses = User::where('role', 'student')
            ->whereNotNull('course')
            ->distinct()
            ->pluck('course')
            ->sort()
            ->values();

        $locations = Location::where('is_active', true)
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
    }
}
