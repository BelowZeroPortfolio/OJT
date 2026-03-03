@extends('layouts.admin')

@section('page-title', 'Dashboard')
@section('page-description', 'Monitor attendance across all students and locations')

@section('content')
<div x-data="adminDashboard()" x-init="startPolling()">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <!-- Total Students Card -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Total Students</p>
                    <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.total_students">{{ $statistics['total_students'] }}</p>
                </div>
                <div class="p-2 bg-secondary rounded-lg">
                    <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Present Today Card -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Present Today</p>
                    <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.present_today">{{ $statistics['present_today'] }}</p>
                </div>
                <div class="p-2 bg-secondary rounded-lg">
                    <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Absent Today Card -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Absent Today</p>
                    <p class="text-2xl font-bold text-foreground mt-1" x-text="statistics.absent_today">{{ $statistics['absent_today'] }}</p>
                </div>
                <div class="p-2 bg-secondary rounded-lg">
                    <svg class="h-6 w-6 text-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Attendance Trend Chart -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-4">
            <h3 class="text-sm font-semibold text-foreground mb-3">Attendance Trend (Last 7 Days)</h3>
            <div class="relative h-48">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>

        <!-- Location Distribution Chart -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-4">
            <h3 class="text-sm font-semibold text-foreground mb-3">Students by Location</h3>
            <div class="relative h-48">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-card rounded-lg shadow-sm border border-border mb-6" x-data="filterManager()">
        <!-- Collapsed Filter Bar -->
        <div class="p-4 flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1">
                <button @click="showFilters = !showFilters" 
                        type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-secondary hover:bg-accent rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                </button>
                
                <!-- Active Filters Display -->
                <div class="flex items-center gap-2 flex-wrap" x-show="activeFiltersCount > 0">
                    <span class="text-sm text-muted-foreground">Active filters:</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-primary/10 text-foreground" x-text="activeFiltersCount"></span>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <button type="button" 
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-secondary hover:bg-accent rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="hidden sm:inline">Export</span>
                </button>
            </div>
        </div>

        <!-- Expanded Filters -->
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="px-4 pb-4 border-t border-border pt-4">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Student Name Filter -->
                    <div>
                        <label for="student_name" class="block text-xs font-medium text-muted-foreground mb-1.5">Student Name</label>
                        <input type="text" 
                               id="student_name" 
                               name="student_name" 
                               value="{{ $filters['student_name'] ?? '' }}"
                               @input.debounce.500ms="applyFilters()"
                               placeholder="Search..."
                               class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground">
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label for="course" class="block text-xs font-medium text-muted-foreground mb-1.5">Course</label>
                        <select id="course" 
                                name="course"
                                @change="applyFilters()"
                                class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                            <option value="{{ $course }}" {{ ($filters['course'] ?? '') == $course ? 'selected' : '' }}>
                                {{ $course }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Filter -->
                    <div>
                        <label for="location_id" class="block text-xs font-medium text-muted-foreground mb-1.5">Location</label>
                        <select id="location_id" 
                                name="location_id"
                                @change="applyFilters()"
                                class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ ($filters['location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="date_from" class="block text-xs font-medium text-muted-foreground mb-1.5">Date Range</label>
                        <div class="flex gap-2">
                            <input type="date" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="{{ $filters['date_from'] ?? '' }}"
                                   @change="applyFilters()"
                                   class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground">
                            <input type="date" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="{{ $filters['date_to'] ?? '' }}"
                                   @change="applyFilters()"
                                   class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground">
                        </div>
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="flex justify-end">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear all filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Attendance Records</h3>
        </div>
        
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Time In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Time Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border" x-ref="tableBody">
                    @forelse($attendanceRecords as $record)
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ $record->user->name }}</div>
                            <div class="text-sm text-muted-foreground">{{ $record->user->course }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->location->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->time_in->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ $record->time_out ? $record->time_out->format('h:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($record->time_out)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                                    Complete
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground border border-border">
                                    In Progress
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-muted-foreground">
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
                        <p class="text-sm font-medium text-foreground">{{ $record->user->name }}</p>
                        <p class="text-xs text-muted-foreground">{{ $record->user->course }}</p>
                    </div>
                    @if($record->time_out)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                            Complete
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground border border-border">
                            In Progress
                        </span>
                    @endif
                </div>
                <div class="text-sm">
                    <p class="text-muted-foreground">Location</p>
                    <p class="text-foreground">{{ $record->location->name }}</p>
                </div>
                <div class="text-sm">
                    <p class="text-muted-foreground">Date</p>
                    <p class="text-foreground">{{ $record->date->format('M d, Y') }}</p>
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
function adminDashboard() {
    return {
        statistics: {
            total_students: {{ $statistics['total_students'] }},
            present_today: {{ $statistics['present_today'] }},
            absent_today: {{ $statistics['absent_today'] }}
        },
        pollingInterval: null,
        attendanceTrendChart: null,
        locationChart: null,

        init() {
            this.initCharts();
        },

        startPolling() {
            // Poll for updates every 15 seconds
            this.pollingInterval = setInterval(() => {
                this.fetchUpdates();
            }, 15000);
        },

        initCharts() {
            // Attendance Trend Chart
            const trendCtx = document.getElementById('attendanceTrendChart');
            if (trendCtx) {
                this.attendanceTrendChart = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Present',
                            data: [45, 52, 48, 55, 50, 30, 25],
                            borderColor: '#F0FA43',
                            backgroundColor: 'rgba(240, 250, 67, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#2E2E2E'
                                },
                                ticks: {
                                    color: '#999999'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#999999'
                                }
                            }
                        }
                    }
                });
            }

            // Location Distribution Chart
            const locationCtx = document.getElementById('locationChart');
            if (locationCtx) {
                this.locationChart = new Chart(locationCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Main Office', 'Branch A', 'Branch B', 'Remote'],
                        datasets: [{
                            data: [35, 25, 20, 20],
                            backgroundColor: [
                                '#F0FA43',      // Bright yellow
                                '#FAFAFA',      // White
                                '#999999',      // Gray
                                '#82dcffff'       // Dark gray
                            ],
                            borderColor: '#080808',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#FFFFFF',
                                    padding: 10,
                                    font: {
                                        size: 11,
                                        family: 'Inter, sans-serif',
                                        weight: '500'
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    generateLabels: function(chart) {
                                        const data = chart.data;
                                        if (data.labels.length && data.datasets.length) {
                                            return data.labels.map((label, i) => {
                                                const value = data.datasets[0].data[i];
                                                return {
                                                    text: `${label}: ${value}%`,
                                                    fillStyle: data.datasets[0].backgroundColor[i],
                                                    strokeStyle: data.datasets[0].backgroundColor[i],
                                                    fontColor: '#FFFFFF',
                                                    hidden: false,
                                                    index: i
                                                };
                                            });
                                        }
                                        return [];
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + '%';
                                    }
                                },
                                backgroundColor: '#1E1E1E',
                                titleColor: '#FFFFFF',
                                bodyColor: '#FFFFFF',
                                borderColor: '#F0FA43',
                                borderWidth: 1,
                                padding: 12,
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                }
                            }
                        }
                    }
                });
            }
        },

        async fetchUpdates() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const queryString = urlParams.toString();
                const url = '/api/admin/attendance' + (queryString ? '?' + queryString : '');
                
                const response = await fetch(url, {
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
            this.statistics.total_students = data.statistics.total_students;
            this.statistics.present_today = data.statistics.present_today;
            this.statistics.absent_today = data.statistics.absent_today;

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

function filterManager() {
    return {
        showFilters: {{ !empty($filters['student_name']) || !empty($filters['course']) || !empty($filters['location_id']) || !empty($filters['date_from']) || !empty($filters['date_to']) ? 'true' : 'false' }},
        
        get activeFiltersCount() {
            let count = 0;
            const form = this.$el.querySelector('form');
            if (!form) return count;
            
            const inputs = form.querySelectorAll('input[type="text"], input[type="date"], select');
            inputs.forEach(input => {
                if (input.value && input.value !== '') {
                    count++;
                }
            });
            return count;
        },

        applyFilters() {
            const form = this.$el.querySelector('form');
            if (form) {
                form.submit();
            }
        }
    }
}
</script>
@endsection
