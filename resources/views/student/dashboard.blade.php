@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" 
     x-data="studentDashboard()" 
     x-init="startPolling()">
    
    <!-- Page Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-foreground">My Attendance</h2>
        <p class="mt-1 text-sm text-muted-foreground">View your attendance records and statistics</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Total Hours Card -->
        <div class="bg-card rounded-lg border border-border p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Total Hours</p>
                    <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.total_hours">{{ $statistics['total_hours'] }}</p>
                </div>
                <div class="p-2 bg-secondary rounded-lg">
                    <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Days Present Card -->
        <div class="bg-card rounded-lg border border-border p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Days Present</p>
                    <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.days_present">{{ $statistics['days_present'] }}</p>
                </div>
                <div class="p-2 bg-secondary rounded-lg">
                    <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-card rounded-lg border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Attendance Records</h3>
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
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border" x-ref="tableBody">
                    @forelse($attendanceRecords as $record)
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->time_in->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->time_out ? $record->time_out->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->location->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->total_hours ? number_format($record->total_hours, 2) : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-muted-foreground">
                            No attendance records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-border">
            @forelse($attendanceRecords as $record)
            <div class="p-4 space-y-3 hover:bg-muted/50 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-foreground">{{ $record->date->format('M d, Y') }}</p>
                        <p class="text-xs text-muted-foreground">{{ $record->location->name }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-foreground border border-border">
                        {{ $record->total_hours ? number_format($record->total_hours, 2) . ' hrs' : 'In Progress' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-muted-foreground">Time In</p>
                        <p class="text-foreground">{{ $record->time_in->format('h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-muted-foreground">Time Out</p>
                        <p class="text-foreground">{{ $record->time_out ? $record->time_out->format('h:i A') : '-' }}</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm text-muted-foreground">
                No attendance records found.
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($attendanceRecords->hasPages())
        <div class="px-6 py-4 border-t border-border">
            {{ $attendanceRecords->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function studentDashboard() {
    return {
        statistics: {
            total_hours: {{ $statistics['total_hours'] }},
            days_present: {{ $statistics['days_present'] }}
        },
        pollingInterval: null,

        startPolling() {
            // Poll for updates every 30 seconds
            this.pollingInterval = setInterval(() => {
                this.fetchUpdates();
            }, 30000);
        },

        async fetchUpdates() {
            try {
                const response = await fetch('/api/student/attendance', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.getToken()
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.updateDashboard(data);
                }
            } catch (error) {
                console.error('Failed to fetch updates:', error);
            }
        },

        updateDashboard(data) {
            // Update statistics
            this.statistics.total_hours = data.statistics.total_hours;
            this.statistics.days_present = data.statistics.days_present;

            // Optionally reload the page if there are new records
            if (data.has_new_records) {
                window.location.reload();
            }
        },

        getToken() {
            // Get token from localStorage or cookie
            return localStorage.getItem('auth_token') || '';
        }
    }
}
</script>
@endsection
