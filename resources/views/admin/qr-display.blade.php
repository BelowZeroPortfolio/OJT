@extends('layouts.admin')

@section('page-title', 'QR Code Display')
@section('page-description', $location->name)

@section('content')
<div class="min-h-screen bg-background flex items-center justify-center p-4">
    <div class="max-w-6xl w-full">
        <div class="bg-card rounded-2xl shadow-2xl border border-border p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-foreground mb-2">{{ $location->name }}</h1>
                <p class="text-muted-foreground text-lg">Scan QR Code for Attendance</p>
            </div>
            
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Left Column: QR Code -->
                <div class="flex justify-center">
                    <div id="qr-container" class="bg-white p-8 rounded-2xl shadow-lg border-4 border-primary">
                        <div id="qr-image" class="w-80 h-80 flex items-center justify-center">
                            <div class="animate-pulse text-muted-foreground">Loading QR Code...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Timer & Info -->
                <div class="space-y-6">
                    <!-- Countdown Timer -->
                    <div class="bg-secondary/50 rounded-xl p-8 border border-border text-center">
                        <p class="text-sm font-medium text-muted-foreground mb-3">Refreshes in</p>
                        <p id="countdown" class="text-7xl font-bold text-primary mb-3">
                            <span class="animate-pulse">...</span>
                        </p>
                        <p class="text-sm text-muted-foreground">seconds</p>
                    </div>
                    
                    <!-- Status Indicator -->
                    <div class="bg-card rounded-xl p-6 border border-border">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <div id="status-dot" class="w-4 h-4 bg-green-500 rounded-full animate-pulse"></div>
                            <p id="status-text" class="text-base font-medium text-foreground">Active</p>
                        </div>
                        
                        <!-- Info -->
                        <div class="text-sm text-muted-foreground space-y-2 text-center">
                            <p class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Location ID: {{ $location->id }}
                            </p>
                            <p class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Auto-refresh: Enabled
                            </p>
                            <p id="last-updated" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Last updated: --
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const locationId = {{ $location->id }};
    let expiryTime = null; // Store actual expiry timestamp
    let countdownInterval;
    
    async function fetchQrCode() {
        const statusDot = document.getElementById('status-dot');
        const statusText = document.getElementById('status-text');
        const countdownEl = document.getElementById('countdown');
        
        try {
            statusText.textContent = 'Refreshing...';
            statusDot.classList.remove('bg-green-500');
            statusDot.classList.add('bg-yellow-500');
            
            // Single optimized call - gets QR image with expiry info in headers
            const response = await fetch(`/admin/qr-codes/image/${locationId}`, {
                headers: {
                    'Accept': 'image/svg+xml',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const svgText = await response.text();
                
                // Get expiry timestamp from response headers (ISO 8601 format)
                const expiresAtHeader = response.headers.get('X-Token-Expires-At');
                
                if (expiresAtHeader) {
                    // Parse the ISO timestamp and store it
                    expiryTime = new Date(expiresAtHeader);
                } else {
                    // Fallback: use expires-in seconds
                    const expiresIn = parseInt(response.headers.get('X-Token-Expires-In') || '60');
                    expiryTime = new Date(Date.now() + (expiresIn * 1000));
                }
                
                // Update QR code
                document.getElementById('qr-image').innerHTML = svgText;
                
                // Update status
                statusDot.classList.remove('bg-yellow-500', 'bg-red-500');
                statusDot.classList.add('bg-green-500');
                statusText.textContent = 'Active';
                
                // Update last updated time (in local timezone)
                const now = new Date();
                document.getElementById('last-updated').innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Last updated: ${now.toLocaleTimeString('en-PH', { timeZone: 'Asia/Manila' })}
                `;
                
                // Start countdown based on actual expiry time
                startCountdown();
            } else {
                throw new Error('Failed to fetch QR code');
            }
        } catch (error) {
            console.error('Error fetching QR code:', error);
            statusDot.classList.remove('bg-green-500', 'bg-yellow-500');
            statusDot.classList.add('bg-red-500');
            statusText.textContent = 'Error - Retrying...';
            
            // Retry after 5 seconds
            setTimeout(fetchQrCode, 5000);
        }
    }
    
    function startCountdown() {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        
        countdownInterval = setInterval(() => {
            // Calculate remaining seconds based on actual expiry time
            const now = Date.now();
            const remainingMs = expiryTime - now;
            const remainingSeconds = Math.max(0, Math.ceil(remainingMs / 1000));
            
            document.getElementById('countdown').textContent = remainingSeconds;
            
            // Refresh when expired
            if (remainingSeconds <= 0) {
                clearInterval(countdownInterval);
                fetchQrCode();
            }
        }, 1000);
    }
    
    // Initial load
    fetchQrCode();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
    });
</script>
@endsection
