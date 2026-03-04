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
    <div class="bg-gray-800/30 backdrop-blur-sm border-b border-gray-700/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Welcome back, {{ Auth::user()->name }}</h1>
                    <p class="text-gray-300 mt-1">{{ Auth::user()->course }} • {{ Auth::user()->location->name ?? 'No location assigned' }}</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-400">{{ now('Asia/Manila')->format('l, F j, Y') }}</div>
                    <div class="text-lg font-semibold text-white current-time">{{ now('Asia/Manila')->format('g:i A') }} PHT</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
         x-data="studentDashboard()">

        <!-- RFID Scanner Card -->
        <div class="mb-8" x-data="rfidScanner()">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 shadow-2xl">
                <div class="text-center">
                    <!-- Scanner Icon -->
                    <div class="relative mb-6">
                        <div class="w-20 h-20 mx-auto bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mb-4" 
                             :class="scannerStatus === 'scanning' ? 'animate-pulse' : ''">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <!-- Scanning Animation Rings -->
                        <div x-show="scannerStatus === 'scanning'" class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 border-2 border-white/30 rounded-full animate-ping"></div>
                            <div class="absolute w-24 h-24 border-2 border-white/20 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-white mb-2">RFID Attendance Scanner</h2>
                    <p class="text-blue-100 mb-6">Tap your RFID card or enter your ID number</p>

                    <!-- Status Messages -->
                    <div class="mb-6 min-h-[24px]">
                        <div x-show="scannerStatus === 'ready'" class="text-blue-100">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Ready to scan
                        </div>
                        <div x-show="scannerStatus === 'scanning'" class="text-white animate-pulse">
                            <svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </div>
                        <div x-show="scannerStatus === 'success'" class="text-green-300">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="successMessage"></span>
                        </div>
                        <div x-show="scannerStatus === 'error'" class="text-red-300">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span x-text="errorMessage"></span>
                        </div>
                    </div>

                    <!-- RFID Input -->
                    <div class="max-w-md mx-auto mb-6">
                        <div class="relative">
                            <input 
                                type="text" 
                                x-model="rfidInput"
                                @keyup.enter="processRfid()"
                                @input="handleRfidInput()"
                                placeholder="Scan card or enter RFID number"
                                class="w-full px-6 py-4 text-center text-lg bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all text-white placeholder:text-white/60"
                                :disabled="scannerStatus === 'scanning'"
                                x-ref="rfidInput">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg x-show="scannerStatus === 'scanning'" class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 justify-center mb-6">
                        <button 
                            @click="processRfid()"
                            :disabled="!rfidInput || scannerStatus === 'scanning'"
                            class="px-8 py-3 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Record Attendance
                        </button>
                        <button 
                            @click="clearInput()"
                            class="px-6 py-3 bg-white/10 backdrop-blur-sm text-white rounded-xl font-medium hover:bg-white/20 transition-all duration-200 border border-white/20">
                            Clear
                        </button>
                    </div>

                    <!-- Current Status -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="text-sm text-blue-100 mb-1">Today's Status</div>
                        <div class="text-lg font-semibold text-white" x-text="currentStatus">
                            {{ $currentStatus ?? 'Not checked in today' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Hours -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50 hover:border-gray-600/50 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">Total Hours</p>
                        <p class="text-3xl font-bold text-white mt-2" x-text="statistics.total_hours">{{ $statistics['total_hours'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">This month</p>
                    </div>
                    <div class="p-3 bg-blue-500/20 rounded-lg">
                        <svg class="h-8 w-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Days Present -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50 hover:border-gray-600/50 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">Days Present</p>
                        <p class="text-3xl font-bold text-white mt-2" x-text="statistics.days_present">{{ $statistics['days_present'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">This month</p>
                    </div>
                    <div class="p-3 bg-green-500/20 rounded-lg">
                        <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Average Hours -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50 hover:border-gray-600/50 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">Avg Hours/Day</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ $statistics['days_present'] > 0 ? number_format($statistics['total_hours'] / $statistics['days_present'], 1) : '0.0' }}</p>
                        <p class="text-xs text-gray-500 mt-1">Daily average</p>
                    </div>
                    <div class="p-3 bg-purple-500/20 rounded-lg">
                        <svg class="h-8 w-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50 hover:border-gray-600/50 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400">This Week</p>
                        <p class="text-3xl font-bold text-white mt-2">{{ $attendanceRecords->where('date', '>=', now('Asia/Manila')->startOfWeek())->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Days attended</p>
                    </div>
                    <div class="p-3 bg-yellow-500/20 rounded-lg">
                        <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Records -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-800/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-white">Recent Attendance</h3>
                        <p class="text-sm text-gray-400 mt-1">Your latest attendance records</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-400">Live updates</span>
                    </div>
                </div>
            </div>
            
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-800/30">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time In</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time Out</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Hours</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50" x-ref="tableBody">
                        @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ $record->date->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $record->date->format('l') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $record->time_in->setTimezone('Asia/Manila')->format('h:i A') }} PHT</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $record->time_out ? $record->time_out->setTimezone('Asia/Manila')->format('h:i A') . ' PHT' : '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-white">{{ $record->location->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">
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
                                        <span class="text-blue-400">{{ $currentHours }}</span>
                                        <div class="text-xs text-gray-500">ongoing</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($record->time_out)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Complete
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                        <svg class="w-3 h-3 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        In Progress
                                    </span>
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
                                    <p class="text-sm">Start by scanning your RFID card above</p>
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                In Progress
                            </span>
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
                                    <span class="text-blue-400">{{ $currentHours }}</span>
                                    <div class="text-xs text-gray-500">ongoing</div>
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
                        <p class="text-sm">Start by scanning your RFID card above</p>
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
    const supabaseUrl = '{{ env("VITE_SUPABASE_URL") }}';
    const supabaseAnonKey = '{{ env("VITE_SUPABASE_ANON_KEY") }}';
    
    if (supabaseUrl && supabaseAnonKey && window.supabase) {
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

function rfidScanner() {
    return {
        rfidInput: '',
        scannerStatus: 'ready', // ready, scanning, success, error
        successMessage: '',
        errorMessage: '',
        currentStatus: '{{ $currentStatus ?? "Not checked in today" }}',
        realtimeSubscription: null,
        supabaseAvailable: false,

        init() {
            // Auto-focus the RFID input
            this.$refs.rfidInput.focus();
            
            // Listen for RFID card scans (usually end with Enter)
            document.addEventListener('keydown', (e) => {
                if (e.target !== this.$refs.rfidInput && e.key.match(/[0-9a-zA-Z]/)) {
                    // If typing and not focused on input, focus it
                    this.$refs.rfidInput.focus();
                    this.rfidInput = e.key;
                }
            });

            // Start real-time updates
            this.startRealTimeUpdates();
        },

        startRealTimeUpdates() {
            // Try to initialize Supabase
            this.supabaseAvailable = initializeSupabase();
            
            if (this.supabaseAvailable && supabaseClient) {
                console.log('🚀 Starting Supabase real-time subscriptions...');
                this.setupSupabaseSubscription();
            } else {
                console.log('⚠️ Supabase not available, falling back to periodic updates');
                this.startPeriodicUpdates();
            }

            // Update time display every second
            setInterval(() => {
                this.updateCurrentTime();
            }, 1000);
        },

        setupSupabaseSubscription() {
            if (!supabaseClient) return;
            
            const userId = {{ Auth::id() }};
            
            this.realtimeSubscription = supabaseClient
                .channel(`attendance_changes_${userId}`)
                .on(
                    'postgres_changes',
                    {
                        event: '*',
                        schema: 'public',
                        table: 'attendance_records',
                        filter: `user_id=eq.${userId}`
                    },
                    (payload) => {
                        console.log('📡 Real-time update received:', payload);
                        this.handleRealtimeUpdate(payload);
                    }
                )
                .subscribe((status) => {
                    console.log('Subscription status:', status);
                    if (status === 'SUBSCRIBED') {
                        console.log('✅ Successfully subscribed to attendance updates');
                    } else if (status === 'CHANNEL_ERROR') {
                        console.error('❌ Failed to subscribe to attendance updates');
                        // Fallback to periodic updates
                        this.startPeriodicUpdates();
                    }
                });
        },

        startPeriodicUpdates() {
            // Fallback: Poll for updates every 30 seconds
            setInterval(() => {
                this.checkForUpdates();
            }, 30000);
        },

        handleRealtimeUpdate(payload) {
            console.log('Processing real-time update:', payload);
            
            // Show notification
            this.showRealtimeNotification(payload.eventType);
            
            // Update statistics and current status
            this.refreshDashboardData();
            
            // Refresh the page to show updated records (simple approach)
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },

        showRealtimeNotification(eventType) {
            let message = '';
            let bgColor = 'bg-blue-500';
            
            switch (eventType) {
                case 'INSERT':
                    message = '✅ Attendance recorded';
                    bgColor = 'bg-green-500';
                    break;
                case 'UPDATE':
                    message = '🔄 Attendance updated';
                    bgColor = 'bg-blue-500';
                    break;
                default:
                    message = '📡 Data updated';
                    bgColor = 'bg-purple-500';
            }
            
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    ${message}
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 4000);
        },

        async refreshDashboardData() {
            try {
                const response = await fetch('/student/check-updates', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.updateStatistics(data.statistics);
                    this.currentStatus = data.currentStatus;
                }
            } catch (error) {
                console.log('Failed to refresh dashboard data:', error);
            }
        },

        updateCurrentTime() {
            // Update the current time display in PHT
            const timeElement = document.querySelector('.current-time');
            if (timeElement) {
                timeElement.textContent = getCurrentPHTTime();
            }
        },

        async checkForUpdates() {
            try {
                const response = await fetch('/student/check-updates', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.hasUpdates) {
                        // Refresh statistics and current status
                        this.updateStatistics(data.statistics);
                        this.currentStatus = data.currentStatus;
                        
                        // Show notification of update
                        this.showUpdateNotification();
                    }
                }
            } catch (error) {
                console.log('Update check failed:', error);
            }
        },

        updateStatistics(newStats) {
            // Update statistics with animation
            const totalHoursEl = document.querySelector('[x-text="statistics.total_hours"]');
            const daysPresentEl = document.querySelector('[x-text="statistics.days_present"]');
            
            if (totalHoursEl && newStats.total_hours !== this.$parent.statistics.total_hours) {
                totalHoursEl.classList.add('animate-pulse');
                setTimeout(() => totalHoursEl.classList.remove('animate-pulse'), 1000);
            }
            
            if (daysPresentEl && newStats.days_present !== this.$parent.statistics.days_present) {
                daysPresentEl.classList.add('animate-pulse');
                setTimeout(() => daysPresentEl.classList.remove('animate-pulse'), 1000);
            }
            
            this.$parent.statistics = newStats;
        },

        showUpdateNotification() {
            // Show a subtle notification that data was updated
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in';
            notification.textContent = 'Attendance updated';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        },

        handleRfidInput() {
            // Reset status when user starts typing
            if (this.scannerStatus === 'success' || this.scannerStatus === 'error') {
                this.scannerStatus = 'ready';
            }
        },

        async processRfid() {
            if (!this.rfidInput.trim()) {
                this.showError('Please enter or scan an RFID number');
                return;
            }

            this.scannerStatus = 'scanning';
            
            try {
                const response = await fetch('/student/rfid-attendance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        rfid_number: this.rfidInput.trim()
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess(data.message);
                    this.currentStatus = data.status;
                    this.clearInput();
                    
                    // If Supabase real-time is not available, refresh after a delay
                    if (!this.supabaseAvailable) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } else {
                    this.showError(data.message || 'Failed to record attendance');
                }
            } catch (error) {
                console.error('RFID scan error:', error);
                this.showError('Network error. Please try again.');
            }
        },

        showSuccess(message) {
            this.scannerStatus = 'success';
            this.successMessage = message;
            setTimeout(() => {
                if (this.scannerStatus === 'success') {
                    this.scannerStatus = 'ready';
                }
            }, 3000);
        },

        showError(message) {
            this.scannerStatus = 'error';
            this.errorMessage = message;
            setTimeout(() => {
                if (this.scannerStatus === 'error') {
                    this.scannerStatus = 'ready';
                }
            }, 3000);
        },

        clearInput() {
            this.rfidInput = '';
            this.$refs.rfidInput.focus();
        },

        // Cleanup when component is destroyed
        destroy() {
            if (this.realtimeSubscription && supabaseClient) {
                supabaseClient.removeChannel(this.realtimeSubscription);
            }
        }
    }
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