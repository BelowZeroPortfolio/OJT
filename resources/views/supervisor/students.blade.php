<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - {{ $location->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">My Students</h1>
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
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold mb-2">{{ $location->name }}</h2>
            <p class="text-gray-600">Students assigned to this location</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RFID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Today's Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $student)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $student->student_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $student->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $student->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $student->course ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $student->rfid_number ?? 'Not registered' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->attendanceRecords->count() > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Present
                                    </span>
                                    <span class="text-xs text-gray-500 ml-2">
                                        {{ $student->attendanceRecords->first()->time_in->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                        Absent
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No students assigned to this location yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
