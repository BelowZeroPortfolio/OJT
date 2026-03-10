@extends('layouts.student')

@section('content')
<style>
    @keyframes slide-in {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
    .animate-pulse-update {
        animation: pulse 0.5s ease-in-out;
    }
</style>

<div class="min-h-screen">
    <!-- Welcome Header -->
    <div class="bg-card border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Welcome back, {{ Auth::user()->name }}</h1>
                    <p class="text-muted-foreground mt-1">{{ Auth::user()->course }} • {{ Auth::user()->location->name ?? 'No location assigned' }}</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-muted-foreground">{{ now('Asia/Manila')->format('l, F j, Y') }}</div>
                    <div class="text-lg font-semibold text-foreground current-time">{{ now('Asia/Manila')->format('g:i A') }} PHT</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
         x-data="studentDashboard()">

        <!-- QR Code Scanner Quick Access -->
        @include('components.qr-attendance-links')

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Hours -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Total Hours</p>
                        <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.total_hours">{{ $statistics['total_hours'] }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">This month</p>
                    </div>
                    <div class="p-2 bg-secondary rounded-lg">
                        <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Days Present -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Days Present</p>
                        <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.days_present">{{ $statistics['days_present'] }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">This month</p>
                    </div>
                    <div class="p-2 bg-secondary rounded-lg">
                        <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Hours -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Avg Hours/Day</p>
                        <p class="text-2xl font-bold text-foreground mt-1">{{ $statistics['days_present'] > 0 ? number_format($statistics['total_hours'] / $statistics['days_present'], 1) : '0.0' }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Daily average</p>
                    </div>
                    <div class="p-2 bg-secondary rounded-lg">
                        <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">This Week</p>
                        <p class="text-2xl font-bold text-foreground mt-1">{{ $attendanceRecords->where('date', '>=', now('Asia/Manila')->startOfWeek())->count() }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">Days attended</p>
                    </div>
                    <div class="p-2 bg-secondary rounded-lg">
                        <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Records -->
        <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border">
                <h3 class="text-lg font-semibold text-foreground">Recent Attendance</h3>
                <p class="text-sm text-muted-foreground mt-1">Your latest attendance records</p>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Time In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Time Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Hours</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border" x-ref="tableBody">
                        @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-muted/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-foreground">{{ $record->date->format('M d, Y') }}</div>
                                <div class="text-xs text-muted-foreground">{{ $record->date->format('l') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                {{ $record->time_in->setTimezone('Asia/Manila')->format('h:i A') }} PHT
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                {{ $record->time_out ? $record->time_out->setTimezone('Asia/Manila')->format('h:i A') . ' PHT' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                                {{ $record->location->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-foreground">
                                    @if($record->time_out)
                                        @php
                                            $timeIn = $record->time_in->setTimezone('Asia/Manila');
                                            $timeOut = $record->time_out->setTimezone('Asia/Manila');
                                            $diffInMinutes = $timeOut->diffInMinutes($timeIn);
                                            $hours = floor($diffInMinutes / 60);
                                            $minutes = $diffInMinutes % 60;
                                            
                                            if ($diffInMinutes < 1) {
                                                $displayHours = '< 1 min';
                                            } elseif ($diffInMinutes < 60) {
                                                $displayHours = $minutes . ' min';
                                            } elseif ($minutes > 0) {
                                                $displayHours = $hours . 'h ' . $minutes . 'm';
                                            } else {
                                                $displayHours = $hours . '.00 hrs';
                                            }
                                        @endphp
                                        {{ $displayHours }}
                                    @else
                                        @php
                                            $isToday = $record->date->isToday();
                                            $timeIn = $record->time_in->setTimezone('Asia/Manila');
                                            $now = now('Asia/Manila');
                                            $diffInMinutes = $now->diffInMinutes($timeIn);
                                            $hours = floor($diffInMinutes / 60);
                                            $minutes = $diffInMinutes % 60;
                                            
                                            if ($diffInMinutes < 1) {
                                                $currentHours = '< 1 min';
                                            } elseif ($diffInMinutes < 60) {
                                                $currentHours = $minutes . ' min';
                                            } elseif ($minutes > 0) {
                                                $currentHours = $hours . 'h ' . $minutes . 'm';
                                            } else {
                                                $currentHours = $hours . '.00 hrs';
                                            }
                                        @endphp
                                        @if($isToday)
                                            <span class="text-primary">{{ $currentHours }}</span>
                                            <div class="text-xs text-muted-foreground">ongoing</div>
                                        @else
                                            <span class="text-muted-foreground">No time out</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($record->time_out)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                                        Complete
                                    </span>
                                @else
                                    @if($record->date->isToday())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                            <svg class="w-3 h-3 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                            Incomplete
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No attendance records yet</p>
                                    <p class="text-sm">Start by scanning a QR code</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden divide-y divide-gray-700/50">
                @forelse($attendanceRecords as $record)
                <div class="p-6 hover:bg-gray-700/30 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-lg font-medium text-white">{{ $record->date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-400">{{ $record->date->format('l') }}</p>
                        </div>
                        @if($record->time_out)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                Complete
                            </span>
                        @else
                            @if($record->date->isToday())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    In Progress
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                    Incomplete
                                </span>
                            @endif
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-400 mb-1">Time In</p>
                            <p class="text-white font-medium">{{ $record->time_in->setTimezone('Asia/Manila')->format('h:i A') }} PHT</p>
                        </div>
                        <div>
                            <p class="text-gray-400 mb-1">Time Out</p>
                            <p class="text-white font-medium">{{ $record->time_out ? $record->time_out->setTimezone('Asia/Manila')->format('h:i A') . ' PHT' : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 mb-1">Location</p>
                            <p class="text-white font-medium">{{ $record->location->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 mb-1">Hours</p>
                            <p class="text-white font-medium">
                                @if($record->time_out)
                                    @php
                                        $timeIn = $record->time_in->setTimezone('Asia/Manila');
                                        $timeOut = $record->time_out->setTimezone('Asia/Manila');
                                        $diffInMinutes = $timeOut->diffInMinutes($timeIn);
                                        $hours = floor($diffInMinutes / 60);
                                        $minutes = $diffInMinutes % 60;
                                        
                                        if ($diffInMinutes < 1) {
                                            $displayHours = '< 1 min';
                                        } elseif ($diffInMinutes < 60) {
                                            $displayHours = $minutes . ' min';
                                        } elseif ($minutes > 0) {
                                            $displayHours = $hours . 'h ' . $minutes . 'm';
                                        } else {
                                            $displayHours = $hours . '.00 hrs';
                                        }
                                    @endphp
                                    {{ $displayHours }}
                                @else
                                    @php
                                        $isToday = $record->date->isToday();
                                        $timeIn = $record->time_in->setTimezone('Asia/Manila');
                                        $now = now('Asia/Manila');
                                        $diffInMinutes = $now->diffInMinutes($timeIn);
                                        $hours = floor($diffInMinutes / 60);
                                        $minutes = $diffInMinutes % 60;
                                        
                                        if ($diffInMinutes < 1) {
                                            $currentHours = '< 1 min';
                                        } elseif ($diffInMinutes < 60) {
                                            $currentHours = $minutes . ' min';
                                        } elseif ($minutes > 0) {
                                            $currentHours = $hours . 'h ' . $minutes . 'm';
                                        } else {
                                            $currentHours = $hours . '.00 hrs';
                                        }
                                    @endphp
                                    @if($isToday)
                                        <span class="text-blue-400">{{ $currentHours }}</span>
                                        <div class="text-xs text-gray-500">ongoing</div>
                                    @else
                                        <span class="text-gray-500">No time out</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center">
                    <div class="text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2 a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-lg font-medium">No attendance records yet</p>
                        <p class="text-sm">Start by scanning a QR code</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($attendanceRecords->hasPages())
            <div class="px-6 py-4 border-t border-gray-700/50 bg-gray-800/30">
                {{ $attendanceRecords->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Supabase Real-time functionality (inline implementation)
let supabaseClient = null;

// Initialize Supabase client if available
function initializeSupabase() {
    const supabaseUrl = '{{ config("app.supabase_url") ?? env("SUPABASE_URL") }}';
    const supabaseAnonKey = '{{ config("app.supabase_anon_key") ?? env("SUPABASE_ANON_KEY") }}';
    
    console.log('Supabase URL:', supabaseUrl);
    console.log('Supabase Key exists:', !!supabaseAnonKey);
    
    if (supabaseUrl && supabaseAnonKey && window.supabase) {
        try {
            supabaseClient = window.supabase.createClient(supabaseUrl, supabaseAnonKey, {
                realtime: {
                    params: {
                        eventsPerSecond: 10
                    }
                },
                auth: {
                    persistSession: false
                }
            });
            console.log('✅ Supabase client initialized');
            return true;
        } catch (error) {
            console.error('❌ Failed to initialize Supabase:', error);
            return false;
        }
    }
    
    if (!supabaseUrl || !supabaseAnonKey) {
        console.log('⚠️ Missing Supabase environment variables');
        console.log('URL:', supabaseUrl);
        console.log('Key exists:', !!supabaseAnonKey);
    }
    if (!window.supabase) {
        console.log('⚠️ Supabase library not loaded');
    }
    
    console.log('⚠️ Supabase not available, using fallback mode');
    return false;
}

// Helper functions for PHT timezone
function formatTimePHT(timestamp) {
    const date = new Date(timestamp);
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date) + ' PHT';
}

function formatDatePHT(timestamp) {
    const date = new Date(timestamp);
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
}

function getCurrentPHTTime() {
    const now = new Date();
    return new Intl.DateTimeFormat('en-PH', {
        timeZone: 'Asia/Manila',
        hour: 'numeric',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    }).format(now) + ' PHT';
}

function studentDashboard() {
    return {
        statistics: {
            total_hours: {{ $statistics['total_hours'] }},
            days_present: {{ $statistics['days_present'] }}
        },

        init() {
            // Dashboard initialization - no polling needed as RFID scanner handles updates
            console.log('📊 Student dashboard initialized');
        }
    }
}
</script>
@endsection