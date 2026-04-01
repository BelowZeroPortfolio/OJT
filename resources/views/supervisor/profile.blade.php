<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Profile Settings</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('supervisor.dashboard') }}" class="hover:underline">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Profile Information</h2>
                
                <form method="POST" action="{{ route('supervisor.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Name
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $supervisor->name) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $supervisor->email) }}"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role (Read-only) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role
                        </label>
                        <input type="text" 
                               value="Supervisor"
                               disabled
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                    </div>

                    <!-- Location (Read-only) -->
                    @if($location)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Assigned Location
                        </label>
                        <input type="text" 
                               value="{{ $location->name }}"
                               disabled
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
                        <p class="mt-1 text-xs text-gray-500">{{ $location->location_code }}</p>
                    </div>
                    @endif

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Change Password</h2>
                
                <form method="POST" action="{{ route('supervisor.profile.update-password') }}">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Current Password
                        </label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                        Change Password
                    </button>
                </form>

                <!-- Password Tips -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Password Tips</h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• Use at least 8 characters</li>
                        <li>• Include uppercase and lowercase letters</li>
                        <li>• Add numbers and special characters</li>
                        <li>• Avoid common words or patterns</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Account Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-600">Account Created</p>
                    <p class="font-medium">{{ $supervisor->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Last Updated</p>
                    <p class="font-medium">{{ $supervisor->updated_at->format('M d, Y h:i A') }}</p>
                </div>
                @if($location)
                <div>
                    <p class="text-gray-600">Location Address</p>
                    <p class="font-medium">{{ $location->address ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Location Status</p>
                    <p class="font-medium">
                        <span class="px-2 py-1 text-xs rounded-full {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $location->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
