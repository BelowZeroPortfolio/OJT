<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Location Assigned</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Supervisor Dashboard</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">No Location Assigned</h2>
            <p class="text-gray-600">You are not currently assigned to supervise any location.</p>
            <p class="text-gray-600 mt-2">Please contact the administrator for assistance.</p>
        </div>
    </div>
</body>
</html>
