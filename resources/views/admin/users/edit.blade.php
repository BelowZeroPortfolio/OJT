@extends('layouts.admin')

@section('page-title', 'Edit Student')
@section('page-description', 'Update student information and training location assignment')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Form Card -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name', $user->name) }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Student ID -->
            <div>
                <label for="student_id" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Student ID <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="student_id" 
                       name="student_id" 
                       value="{{ old('student_id', $user->student_id) }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('student_id') border-red-500 @enderror">
                @error('student_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email', $user->email) }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Password
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-muted-foreground">Leave blank to keep current password. Minimum 8 characters if changing.</p>
            </div>

            <!-- Course -->
            <div>
                <label for="course" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Course <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="course" 
                       name="course" 
                       value="{{ old('course', $user->course) }}"
                       required
                       class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground @error('course') border-red-500 @enderror">
                @error('course')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Assigned Location -->
            <div>
                <label for="assigned_location_id" class="block text-xs font-medium text-muted-foreground mb-1.5">
                    Assigned Training Location <span class="text-red-500">*</span>
                </label>
                <select id="assigned_location_id" 
                        name="assigned_location_id"
                        required
                        class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground @error('assigned_location_id') border-red-500 @enderror">
                    <option value="">Select a location</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ old('assigned_location_id', $user->assigned_location_id) == $location->id ? 'selected' : '' }}>
                        {{ $location->name }} ({{ $location->location_code }})
                    </option>
                    @endforeach
                </select>
                @error('assigned_location_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex gap-3 pt-4 border-t border-border">
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 font-medium transition-colors">
                    Update Student
                </button>
                <a href="{{ route('admin.users.index') }}" 
                   class="flex-1 sm:flex-none px-6 py-2 bg-secondary text-foreground rounded-lg hover:bg-accent focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-center font-medium transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
