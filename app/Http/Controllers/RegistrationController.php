<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Http\Requests\StudentRegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        $locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('register', compact('locations'));
    }

    /**
     * Handle student registration.
     */
    public function register(StudentRegistrationRequest $request)
    {
        $validated = $request->validated();

        // Handle new location creation
        if ($validated['assigned_location_id'] === 'other') {
            $location = Location::create([
                'name' => $validated['new_location_name'],
                'address' => $validated['new_location_address'],
                'location_code' => 'LOC-' . strtoupper(substr(md5($validated['new_location_name']), 0, 6)),
                'is_active' => false, // Requires admin approval
            ]);
            
            $validated['assigned_location_id'] = $location->id;
        }

        // Create the student user
        $user = User::create([
            'name' => $validated['name'],
            'student_id' => $validated['student_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'course' => $validated['course'],
            'assigned_location_id' => $validated['assigned_location_id'],
            'role' => 'student',
        ]);

        // Log the user in
        auth()->login($user);

        return redirect()->route('student.dashboard')
            ->with('success', 'Registration successful! Welcome to OJT Attendance System.');
    }
}
