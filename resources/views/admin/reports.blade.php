@extends('layouts.admin')

@section('page-title', 'Reports')
@section('page-description', 'Generate and print attendance reports')

@section('content')
<div x-data="reportsPage()" x-init="init()">
    <!-- Loading Overlay -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-background/80 backdrop-blur-sm z-50 flex items-center justify-center"
         style="display: none;">
        <div class="bg-card rounded-xl border border-border/50 p-6 shadow-lg">
            <div class="flex flex-col items-center gap-3">
                <div class="relative w-12 h-12">
                    <div class="absolute inset-0 border-4 border-muted rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>
                <p class="text-sm font-medium text-foreground">Loading reports...</p>
            </div>
        </div>
    </div>

    <!-- Minimalist Filter Bar -->
    <div class="bg-card rounded-xl border border-border/50 mb-6 no-print overflow-hidden">
        <div class="p-4">
            <!-- Compact Filter Row -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" 
                           x-model="filters.student_name"
                           @input.debounce.500ms="applyFilters()"
                           placeholder="Search student..."
                           class="w-full pl-9 pr-3 py-2 text-sm bg-background/50 border-0 rounded-lg focus:ring-1 focus:ring-primary/50 transition-all text-foreground placeholder:text-muted-foreground">
                </div>

                <!-- Date Range -->
                <div class="flex items-center gap-2">
                    <input type="date" 
                           x-model="filters.date_from"
                           @change="applyFilters()"
                           class="px-3 py-2 text-sm bg-background/50 border-0 rounded-lg focus:ring-1 focus:ring-primary/50 transition-all text-foreground">
                    <span class="text-muted-foreground">→</span>
                    <input type="date" 
                           x-model="filters.date_to"
                           @change="applyFilters()"
                           class="px-3 py-2 text-sm bg-background/50 border-0 rounded-lg focus:ring-1 focus:ring-primary/50 transition-all text-foreground">
                </div>

                <!-- Course -->
                <select x-model="filters.course"
                        @change="applyFilters()"
                        class="px-3 py-2 text-sm bg-background/50 border-0 rounded-lg focus:ring-1 focus:ring-primary/50 transition-all text-foreground">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                    <option value="{{ $course }}">{{ $course }}</option>
                    @endforeach
                </select>

                <!-- Location -->
                <select x-model="filters.location_id"
                        @change="applyFilters()"
                        class="px-3 py-2 text-sm bg-background/50 border-0 rounded-lg focus:ring-1 focus:ring-primary/50 transition-all text-foreground">
                    <option value="">All Locations</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                    @endforeach
                </select>

                <!-- Actions -->
                <div class="flex items-center gap-2 ml-auto">
                    <button @click="clearFilters()" 
                            x-show="activeFiltersCount > 0"
                            x-transition
                            type="button"
                            class="p-2 text-muted-foreground hover:text-foreground hover:bg-secondary/50 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <button @click="printReport()" 
                            type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-all text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-border/50">
                <span class="text-xs text-muted-foreground">Quick:</span>
                <button type="button" @click="setDateRange('today')" class="px-2 py-1 text-xs bg-secondary/50 hover:bg-secondary text-foreground rounded transition-all">Today</button>
                <button type="button" @click="setDateRange('week')" class="px-2 py-1 text-xs bg-secondary/50 hover:bg-secondary text-foreground rounded transition-all">Week</button>
                <button type="button" @click="setDateRange('month')" class="px-2 py-1 text-xs bg-secondary/50 hover:bg-secondary text-foreground rounded transition-all">Month</button>
                <span x-show="activeFiltersCount > 0" class="ml-auto text-xs text-muted-foreground">
                    <span x-text="activeFiltersCount"></span> filter<span x-show="activeFiltersCount > 1">s</span> active
                </span>
            </div>
        </div>
    </div>

    <!-- Report Content -->
    <div id="printable-report" class="space-y-5">
        <!-- Minimalist Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-card rounded-xl border border-border/50 p-4 hover:border-primary/30 transition-all">
                <p class="text-xs text-muted-foreground mb-1">Students</p>
                <p class="text-2xl font-semibold text-foreground">{{ $totalStudents }}</p>
            </div>
            <div class="bg-card rounded-xl border border-border/50 p-4 hover:border-primary/30 transition-all">
                <p class="text-xs text-muted-foreground mb-1">Attendance</p>
                <p class="text-2xl font-semibold text-foreground">{{ $totalAttendance }}</p>
            </div>
            <div class="bg-card rounded-xl border border-border/50 p-4 hover:border-primary/30 transition-all">
                <p class="text-xs text-muted-foreground mb-1">Avg. Rate</p>
                <p class="text-2xl font-semibold text-foreground">{{ $avgRate }}%</p>
            </div>
            <div class="bg-card rounded-xl border border-border/50 p-4 hover:border-primary/30 transition-all">
                <p class="text-xs text-muted-foreground mb-1">Total Hours</p>
                <p class="text-2xl font-semibold text-foreground">{{ $totalHours }}</p>
            </div>
        </div>

        <!-- Student Attendance Table -->
        <div class="bg-card rounded-xl border border-border/50 overflow-hidden">
            <div class="px-5 py-3 border-b border-border/50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-foreground">Student Attendance</h3>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ $studentAttendance->count() }} students</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">ID</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Name</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Course</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Location</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Days</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Hours</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        @forelse($studentAttendance as $student)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="px-5 py-3 text-sm text-muted-foreground">{{ $student['student_id'] }}</td>
                            <td class="px-5 py-3 text-sm font-medium text-foreground">{{ $student['name'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $student['course'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $student['location'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $student['days_present'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $student['total_hours'] }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-foreground">{{ $student['attendance_rate'] }}%</span>
                                    <div class="w-12 h-1.5 bg-muted rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full transition-all" style="width: {{ min($student['attendance_rate'], 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-muted-foreground">
                                No attendance data found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Location Attendance Table -->
        <div class="bg-card rounded-xl border border-border/50 overflow-hidden">
            <div class="px-5 py-3 border-b border-border/50">
                <h3 class="text-sm font-semibold text-foreground">Location Summary</h3>
                <p class="text-xs text-muted-foreground mt-0.5">{{ $locationAttendance->count() }} locations</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Location</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Students</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Attendance</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-muted-foreground">Avg. Hours</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/30">
                        @forelse($locationAttendance as $location)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="px-5 py-3 text-sm font-medium text-foreground">{{ $location['name'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $location['total_students'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $location['total_attendance'] }}</td>
                            <td class="px-5 py-3 text-sm text-foreground">{{ $location['avg_hours'] }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-muted-foreground">
                                No location data found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function reportsPage() {
    return {
        loading: false,
        filters: {
            student_name: '{{ $filters['student_name'] ?? '' }}',
            date_from: '{{ $filters['date_from'] }}',
            date_to: '{{ $filters['date_to'] }}',
            course: '{{ $filters['course'] ?? '' }}',
            location_id: '{{ $filters['location_id'] ?? '' }}'
        },
        
        init() {
            // Initialize with current filters
        },
        
        get activeFiltersCount() {
            let count = 0;
            const defaultFrom = '{{ Carbon\Carbon::now()->startOfMonth()->toDateString() }}';
            const defaultTo = '{{ Carbon\Carbon::now()->endOfMonth()->toDateString() }}';
            
            if (this.filters.date_from && this.filters.date_from !== defaultFrom) count++;
            if (this.filters.date_to && this.filters.date_to !== defaultTo) count++;
            if (this.filters.course) count++;
            if (this.filters.location_id) count++;
            if (this.filters.student_name) count++;
            return count;
        },

        applyFilters() {
            this.loading = true;
            
            const params = new URLSearchParams();
            
            if (this.filters.student_name) params.set('student_name', this.filters.student_name);
            if (this.filters.date_from) params.set('date_from', this.filters.date_from);
            if (this.filters.date_to) params.set('date_to', this.filters.date_to);
            if (this.filters.course) params.set('course', this.filters.course);
            if (this.filters.location_id) params.set('location_id', this.filters.location_id);
            
            window.location.href = '{{ route('admin.reports') }}?' + params.toString();
        },

        clearFilters() {
            this.loading = true;
            window.location.href = '{{ route('admin.reports') }}';
        },

        setDateRange(range) {
            this.loading = true;
            
            const today = new Date();
            let fromDate, toDate = today;

            switch(range) {
                case 'today':
                    fromDate = today;
                    break;
                case 'week':
                    fromDate = new Date(today);
                    fromDate.setDate(today.getDate() - today.getDay());
                    break;
                case 'month':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
            }

            this.filters.date_from = fromDate.toISOString().split('T')[0];
            this.filters.date_to = toDate.toISOString().split('T')[0];
            this.applyFilters();
        },

        printReport() {
            window.print();
        }
    }
}
</script>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-report, #printable-report * {
        visibility: visible;
    }
    #printable-report {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>
@endsection
