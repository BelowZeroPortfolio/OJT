@extends('layouts.admin')

@section('page-title', 'Add New Location')
@section('page-description', 'Create a new training location for OJT students')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Form Card -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-6">
        <form action="{{ route('admin.locations.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Location Code -->
            <div>
                <label for="location_code" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Location Code <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="location_code" 
                       name="location_code" 
                       value="{{ old('location_code') }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('location_code') border-red-500 @enderror">
                @error('location_code')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-muted-foreground">Unique identifier for this location (e.g., LOC-001)</p>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Location Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Address
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="3"
                          class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Supervisor Information -->
            <div class="border-t border-border pt-6">
                <h3 class="text-sm font-semibold text-foreground mb-4">Supervisor Account</h3>
                
                <!-- Supervisor Name -->
                <div class="mb-4">
                    <label for="supervisor_name" class="block text-xs font-medium text-muted-foreground mb-1.5">
                        Supervisor Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="supervisor_name" 
                           name="supervisor_name" 
                           value="{{ old('supervisor_name') }}"
                           required
                           class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('supervisor_name') border-red-500 @enderror">
                    @error('supervisor_name')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Supervisor Email -->
                <div>
                    <label for="supervisor_email" class="block text-xs font-medium text-muted-foreground mb-1.5">
                        Supervisor Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="supervisor_email" 
                           name="supervisor_email" 
                           value="{{ old('supervisor_email') }}"
                           required
                           class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('supervisor_email') border-red-500 @enderror">
                    @error('supervisor_email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-muted-foreground">Default password will be: supervisor123</p>
                </div>
            </div>

            <!-- Is Active -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-primary focus:ring-ring border-input rounded">
                    <span class="ml-2 text-sm text-foreground">Active</span>
                </label>
                <p class="mt-1 text-xs text-muted-foreground">Inactive locations cannot be assigned to new students</p>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 pt-4 border-t border-border">
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 font-medium transition-colors">
                    Create Location
                </button>
                <a href="{{ route('admin.locations.index') }}" 
                   class="flex-1 sm:flex-none px-6 py-2 bg-secondary text-foreground rounded-lg hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-center font-medium transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
