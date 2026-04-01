<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of locations.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $locations = Location::with('supervisor')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Store a newly created location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_code' => ['required', 'string', 'max:50', 'unique:locations,location_code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'supervisor_name' => ['required', 'string', 'max:255'],
            'supervisor_email' => ['required', 'email', 'unique:users,email'],
        ]);

        // Set default value for is_active if not provided
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

        // Create supervisor account
        $supervisor = \App\Models\User::create([
            'name' => $validated['supervisor_name'],
            'email' => $validated['supervisor_email'],
            'password' => \Hash::make('supervisor123'), // Default password
            'role' => 'supervisor',
        ]);

        // Create location with supervisor
        $location = Location::create([
            'location_code' => $validated['location_code'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'is_active' => $validated['is_active'],
            'supervisor_id' => $supervisor->id,
        ]);
        
        // Invalidate caches after creating location
        $this->cacheService->invalidateOnLocationUpdate($location->id);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location and supervisor account created successfully. Default password: supervisor123');
    }

    /**
     * Show the form for editing the specified location.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\View\View
     */
    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Update the specified location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location_code' => ['required', 'string', 'max:50', Rule::unique('locations')->ignore($location->id)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'supervisor_email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($location->supervisor_id)],
        ]);

        // Handle checkbox value
        $validated['is_active'] = $request->has('is_active') ? (bool) $request->is_active : false;

        // Update supervisor if provided
        if ($request->filled('supervisor_name') || $request->filled('supervisor_email')) {
            if ($location->supervisor) {
                // Update existing supervisor
                $location->supervisor->update([
                    'name' => $validated['supervisor_name'] ?? $location->supervisor->name,
                    'email' => $validated['supervisor_email'] ?? $location->supervisor->email,
                ]);
            } else {
                // Create new supervisor
                $supervisor = \App\Models\User::create([
                    'name' => $validated['supervisor_name'],
                    'email' => $validated['supervisor_email'],
                    'password' => \Hash::make('supervisor123'),
                    'role' => 'supervisor',
                ]);
                $validated['supervisor_id'] = $supervisor->id;
            }
        }

        $location->update([
            'location_code' => $validated['location_code'],
            'name' => $validated['name'],
            'address' => $validated['address'] ?? $location->address,
            'is_active' => $validated['is_active'],
            'supervisor_id' => $validated['supervisor_id'] ?? $location->supervisor_id,
        ]);
        
        // Invalidate caches after updating location
        $this->cacheService->invalidateOnLocationUpdate($location->id);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified location from storage (soft delete).
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Location $location)
    {
        $locationId = $location->id;
        $location->delete();
        
        // Invalidate caches after deleting location
        $this->cacheService->invalidateOnLocationUpdate($locationId);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
