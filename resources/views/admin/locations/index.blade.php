@extends('layouts.admin')

@section('page-title', 'Location Management')
@section('page-description', 'Manage training locations for OJT students')

@section('content')
    <!-- Action Button -->
    <div class="mb-6 flex justify-end">
        <a href="{{ route('admin.locations.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 font-medium transition-colors shadow-sm">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add Location
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 bg-secondary border border-border text-foreground px-4 py-3 rounded-lg flex items-center">
        <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Locations Table -->
    <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Training Locations</h3>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Location Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    @forelse($locations as $location)
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                            {{ $location->location_code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $location->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-foreground">
                            {{ $location->address ?: 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $location->users_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($location->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground border border-border">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.locations.edit', $location) }}" 
                               class="text-primary hover:text-primary/80 mr-3 font-medium">Edit</a>
                            <form action="{{ route('admin.locations.destroy', $location) }}" 
                                  method="POST" 
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this location?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-muted-foreground">
                            No locations found. <a href="{{ route('admin.locations.create') }}" class="text-primary hover:text-primary/80 font-medium">Add your first location</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-border">
            @forelse($locations as $location)
            <div class="p-4 space-y-3 hover:bg-muted/50 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-foreground">{{ $location->name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $location->location_code }}</p>
                    </div>
                    @if($location->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground border border-border">
                            Inactive
                        </span>
                    @endif
                </div>
                <div class="text-sm">
                    <p class="text-muted-foreground">Address</p>
                    <p class="text-foreground">{{ $location->address ?: 'N/A' }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-muted-foreground">Assigned Students</p>
                    <p class="text-foreground">{{ $location->users_count }}</p>
                </div>
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('admin.locations.edit', $location) }}" 
                       class="flex-1 text-center px-3 py-2 bg-primary text-primary-foreground text-sm rounded-lg hover:bg-primary/90 font-medium transition-colors">
                        Edit
                    </a>
                    <form action="{{ route('admin.locations.destroy', $location) }}" 
                          method="POST" 
                          class="flex-1"
                          onsubmit="return confirm('Are you sure you want to delete this location?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-3 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 font-medium transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm text-muted-foreground">
                No locations found. <a href="{{ route('admin.locations.create') }}" class="text-primary hover:text-primary/80 font-medium">Add your first location</a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($locations->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $locations->links() }}
        </div>
        @endif
    </div>
@endsection
