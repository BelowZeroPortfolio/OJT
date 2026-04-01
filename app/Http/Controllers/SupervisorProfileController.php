<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SupervisorProfileController extends Controller
{
    /**
     * Show the supervisor profile page.
     */
    public function edit()
    {
        $supervisor = Auth::user();
        $location = $supervisor->supervisedLocation;
        
        return view('supervisor.profile', compact('supervisor', 'location'));
    }

    /**
     * Update the supervisor profile.
     */
    public function update(Request $request)
    {
        $supervisor = Auth::user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $supervisor->id],
        ]);

        $supervisor->update($validated);

        return redirect()->route('supervisor.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the supervisor password.
     */
    public function updatePassword(Request $request)
    {
        $supervisor = Auth::user();
        
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $supervisor->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('supervisor.profile.edit')
            ->with('success', 'Password updated successfully.');
    }
}
