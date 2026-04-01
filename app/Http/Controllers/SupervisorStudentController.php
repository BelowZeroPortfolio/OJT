<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SupervisorStudentController extends Controller
{
    /**
     * Display list of students for the supervised location.
     */
    public function index()
    {
        $supervisor = Auth::user();
        $location = $supervisor->supervisedLocation;
        
        if (!$location) {
            return view('supervisor.no-location');
        }
        
        // Get students with today's attendance
        $students = $location->students()
            ->with(['attendanceRecords' => function ($query) {
                $query->whereDate('time_in', Carbon::today())
                      ->orderBy('time_in', 'desc');
            }])
            ->orderBy('name')
            ->get();
        
        return view('supervisor.students', compact('location', 'students'));
    }
}
