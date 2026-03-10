@extends('layouts.student')

@section('content')
<div class="min-h-screen bg-background py-8">
    <div class="max-w-md mx-auto px-4">
        <div class="bg-card rounded-2xl shadow-2xl border border-border p-6">
            <h1 class="text-2xl font-bold mb-6 text-center text-foreground">Scan Attendance QR Code</h1>
            
            <!-- Scan Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-muted-foreground mb-3">Scan Type</label>
                <div class="flex gap-3">
                    <button id="btn-time-in" class="flex-1 py-3 px-4 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors shadow-lg">
                        Time In
                    </button>
                    <button id="btn-time-out" class="flex-1 py-3 px-4 bg-secondary text-secondary-foreground rounded-lg font-medium hover:bg-accent transition-colors">
                        Time Out
                    </button>
                </div>
            </div>
            
            <!-- Camera Preview -->
            <div id="camera-container" class="mb-6">
                <div id="reader" class="w-full rounded-xl overflow-hidden border-2 border-border"></div>
            </div>
            
            <!-- Status Messages -->
            <div id="status-message" class="hidden mb-4 p-4 rounded-lg"></div>
            
            <!-- Manual Input (Fallback) -->
            <div class="border-t border-border pt-4">
                <button id="btn-manual-input" class="w-full py-2 text-sm text-muted-foreground hover:text-foreground transition-colors">
                    Enter QR Code Manually
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    let scanType = 'time_in';
    let html5QrCode;
    
    // Scan type buttons
    document.getElementById('btn-time-in').addEventListener('click', () => {
        scanType = 'time_in';
        document.getElementById('btn-time-in').className = 'flex-1 py-3 px-4 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors shadow-lg';
        document.getElementById('btn-time-out').className = 'flex-1 py-3 px-4 bg-secondary text-secondary-foreground rounded-lg font-medium hover:bg-accent transition-colors';
    });
    
    document.getElementById('btn-time-out').addEventListener('click', () => {
        scanType = 'time_out';
        document.getElementById('btn-time-out').className = 'flex-1 py-3 px-4 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors shadow-lg';
        document.getElementById('btn-time-in').className = 'flex-1 py-3 px-4 bg-secondary text-secondary-foreground rounded-lg font-medium hover:bg-accent transition-colors';
    });
    
    // Initialize QR Scanner
    function startScanner() {
        html5QrCode = new Html5Qrcode("reader");
        
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            onScanSuccess,
            onScanError
        ).catch(err => {
            console.error('Scanner error:', err);
            showMessage('Camera access denied or not available', 'error');
        });
    }
    
    async function onScanSuccess(decodedText, decodedResult) {
        // Stop scanning temporarily
        html5QrCode.pause();
        
        try {
            const qrData = JSON.parse(decodedText);
            await submitAttendance(qrData.token);
        } catch (error) {
            showMessage('Invalid QR code format', 'error');
            setTimeout(() => html5QrCode.resume(), 2000);
        }
    }
    
    function onScanError(errorMessage) {
        // Ignore scan errors (happens frequently during scanning)
    }
    
    async function submitAttendance(token) {
        const payload = {
            token: token,
            scan_type: scanType
        };
        
        try {
            const response = await fetch('/student/qr-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '/student/dashboard';
                }, 2000);
            } else {
                showMessage(data.message, 'error');
                setTimeout(() => html5QrCode.resume(), 3000);
            }
        } catch (error) {
            showMessage('Network error. Please try again.', 'error');
            setTimeout(() => html5QrCode.resume(), 2000);
        }
    }
    
    function showMessage(message, type) {
        const statusDiv = document.getElementById('status-message');
        statusDiv.classList.remove('hidden', 'bg-green-500/10', 'bg-red-500/10', 'bg-yellow-500/10', 'text-green-800', 'text-red-800', 'text-yellow-800', 'border', 'border-green-500/30', 'border-red-500/30', 'border-yellow-500/30');
        
        if (type === 'success') {
            statusDiv.classList.add('bg-green-500/10', 'text-green-800', 'border', 'border-green-500/30');
        } else if (type === 'error') {
            statusDiv.classList.add('bg-red-500/10', 'text-red-800', 'border', 'border-red-500/30');
        } else {
            statusDiv.classList.add('bg-yellow-500/10', 'text-yellow-800', 'border', 'border-yellow-500/30');
        }
        
        statusDiv.textContent = message;
    }
    
    // Start scanner on page load
    startScanner();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (html5QrCode) {
            html5QrCode.stop();
        }
    });
</script>
@endsection
