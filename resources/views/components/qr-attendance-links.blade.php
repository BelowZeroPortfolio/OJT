<!-- QR Code Attendance Quick Access -->
@if(Auth::user()->role === 'admin')
    <!-- Admin: QR Display Links -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg p-6 shadow-lg mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-xl font-bold text-white mb-1">QR Code Attendance</h3>
                <p class="text-blue-100 text-sm">Display QR codes for student scanning</p>
            </div>
            <svg class="w-12 h-12 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        </div>
        
        <div class="space-y-2">
            @foreach($locations ?? [] as $location)
                <a href="{{ route('admin.qr-display', $location->id) }}" 
                   target="_blank"
                   class="flex items-center justify-between bg-white/10 hover:bg-white/20 backdrop-blur-sm rounded-lg px-4 py-3 transition-all group">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-white font-medium">{{ $location->name }}</span>
                    </div>
                    <svg class="w-5 h-5 text-white/60 group-hover:text-white group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            @endforeach
            
            @if(empty($locations) || count($locations) === 0)
                <p class="text-white/70 text-sm text-center py-2">No locations available</p>
            @endif
        </div>
    </div>
@elseif(Auth::user()->role === 'student')
    <!-- Student: QR Scanner Link -->
    <a href="{{ route('student.qr-scanner') }}" 
       class="block bg-gradient-to-r from-green-600 to-teal-600 rounded-lg p-6 shadow-lg mb-6 hover:shadow-xl transition-all group">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 backdrop-blur-sm rounded-full p-3 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-1">Scan QR Code</h3>
                    <p class="text-green-100 text-sm">Use your camera to scan attendance QR code</p>
                </div>
            </div>
            <svg class="w-6 h-6 text-white/60 group-hover:text-white group-hover:translate-x-2 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </div>
    </a>
@endif
