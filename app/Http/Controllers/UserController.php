<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of students.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $students = User::where('role', 'student')
            ->with('location')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('students'));
    }

    /**
     * Show the form for creating a new student.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.users.create', compact('locations'));
    }

    /**
     * Store a newly created student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,student_id'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'course' => ['required', 'string', 'max:100'],
            'assigned_location_id' => ['required', 'exists:locations,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'student';

        $user = User::create($validated);
        
        // Invalidate caches after creating user
        $this->cacheService->invalidateOnUserUpdate($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Show the form for editing the specified student.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        // Ensure we're only editing students
        if (!$user->isStudent()) {
            abort(404);
        }

        $locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'locations'));
    }

    /**
     * Update the specified student in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        // Ensure we're only updating students
        if (!$user->isStudent()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'course' => ['required', 'string', 'max:100'],
            'assigned_location_id' => ['required', 'exists:locations,id'],
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['string', 'min:8'],
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);
        
        // Invalidate caches after updating user
        $this->cacheService->invalidateOnUserUpdate($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage (soft delete).
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Ensure we're only deleting students
        if (!$user->isStudent()) {
            abort(404);
        }

        $userId = $user->id;
        $user->delete();
        
        // Invalidate caches after deleting user
        $this->cacheService->invalidateOnUserUpdate($userId);

        return redirect()->route('admin.users.index')
            ->with('success', 'Student deleted successfully.');
    }
}
