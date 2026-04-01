<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $location->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">QR Code Display</h1>
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
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <h2 class="text-2xl font-bold mb-2">{{ $location->name }}</h2>
                <p class="text-gray-600 mb-6">Scan this QR code to mark attendance</p>
                
                <div id="qr-container" class="mb-6">
                    <div class="flex justify-center items-center h-64">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    </div>
                </div>
                
                <div id="qr-info" class="text-sm text-gray-500 hidden">
                    <p>QR Code expires in: <span id="countdown" class="font-bold text-blue-600"></span></p>
                    <p class="mt-2">Auto-refreshes when expired</p>
                </div>
                
                <button onclick="refreshQR()" 
                        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Refresh QR Code
                </button>
            </div>
        </div>
    </div>

    <script>
        let countdownInterval;
        let expiresAt;

        async function loadQRCode() {
            try {
                const response = await fetch('{{ route("supervisor.qr-generate") }}');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('qr-container').innerHTML = 
                        `<img src="${data.qr_image}" alt="QR Code" class="mx-auto max-w-md">`;
                    
                    document.getElementById('qr-info').classList.remove('hidden');
                    expiresAt = new Date(data.expires_at);
                    startCountdown();
                } else {
                    document.getElementById('qr-container').innerHTML = 
                        `<p class="text-red-600">${data.message}</p>`;
                }
            } catch (error) {
                console.error('Error loading QR code:', error);
                document.getElementById('qr-container').innerHTML = 
                    '<p class="text-red-600">Failed to load QR code</p>';
            }
        }

        function startCountdown() {
            if (countdownInterval) clearInterval(countdownInterval);
            
            countdownInterval = setInterval(() => {
                const now = new Date();
                const diff = expiresAt - now;
                
                if (diff <= 0) {
                    clearInterval(countdownInterval);
                    loadQRCode(); // Auto-refresh
                    return;
                }
                
                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                document.getElementById('countdown').textContent = 
                    `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }

        function refreshQR() {
            if (countdownInterval) clearInterval(countdownInterval);
            loadQRCode();
        }

        // Load QR code on page load
        loadQRCode();
    </script>
</body>
</html>
