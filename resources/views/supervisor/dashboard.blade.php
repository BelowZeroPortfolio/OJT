<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - {{ $location->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Supervisor Dashboard</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('supervisor.profile.edit') }}" class="hover:underline">Profile</a>
                <span>{{ $supervisor->name }}</span>
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
        <!-- Location Info -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-2">{{ $location->name }}</h2>
            <p class="text-gray-600">{{ $location->address }}</p>
            <p class="text-sm text-gray-500 mt-2">Location Code: {{ $location->location_code }}</p>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Students</h3>
                <p class="text-3xl font-bold text-blue-600">{{ $totalStudents }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm font-semibold mb-2">Present Today</h3>
                <p class="text-3xl font-bold text-green-600" id="present-count">{{ $presentToday }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm font-semibold mb-2">Attendance Rate</h3>
                <p class="text-3xl font-bold text-purple-600">
                    {{ $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100) : 0 }}%
                </p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
            <div class="flex gap-4">
                <a href="{{ route('supervisor.qr-display') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg">
                    View QR Code
                </a>
                <a href="{{ route('supervisor.students') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">
                    View All Students
                </a>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold mb-4">Recent Attendance</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="attendance-table">
                        @forelse($recentAttendance as $record)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $record->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $record->time_in->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $record->time_out ? $record->time_out->format('M d, Y h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $record->scan_method === 'qr_code' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ strtoupper($record->scan_method) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No attendance records yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh attendance data every 30 seconds
        setInterval(async () => {
            try {
                const response = await fetch('{{ route("supervisor.check-updates") }}');
                const data = await response.json();
                
                document.getElementById('present-count').textContent = data.present_today;
                
                // Optionally reload the page to show new attendance records
                if (data.latest_attendance) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error checking updates:', error);
            }
        }, 30000);
    </script>
</body>
</html>
