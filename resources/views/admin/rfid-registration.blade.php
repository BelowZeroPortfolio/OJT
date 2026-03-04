@extends('layouts.admin')

@section('page-title', 'RFID Card Registration')
@section('page-description', 'Register RFID cards to students for attendance tracking')

@section('content')
<div x-data="rfidRegistration()">
    <!-- Registration Card -->
    <div class="bg-card rounded-lg border border-border p-6 mb-6">
        <div class="text-center">
            <div class="mb-6">
                <div class="w-16 h-16 mx-auto bg-primary/10 rounded-full flex items-center justify-center mb-4" 
                     :class="scannerStatus === 'scanning' ? 'animate-pulse bg-primary/20' : ''">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-foreground mb-2">RFID Card Registration</h3>
                <p class="text-sm text-muted-foreground">Select a student and tap their RFID card to register</p>
            </div>

            <!-- Student Selection -->
            <div class="max-w-md mx-auto mb-6">
                <label for="student_select" class="block text-sm font-medium text-foreground mb-2">Select Student</label>
                
                <select 
                    id="student_select" 
                    x-model="selectedStudentId"
                    @change="onStudentChange()"
                    class="w-full px-4 py-3 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground">
                    <option value="">Choose a student...</option>
                    <template x-for="student in finalFilteredStudents" :key="student.id">
                        <option :value="student.id" x-text="`${student.name} - ${student.course}`"></option>
                    </template>
                </select>
            </div>

            <!-- Selected Student Info -->
            <div x-show="selectedStudent" class="max-w-md mx-auto mb-6 p-4 bg-muted/50 rounded-lg">
                <div class="text-sm">
                    <p class="font-medium text-foreground" x-text="selectedStudent?.name"></p>
                    <p class="text-muted-foreground" x-text="selectedStudent?.course"></p>
                    <div x-show="selectedStudent?.rfid_number" class="mt-2">
                        <p class="text-xs text-muted-foreground">Current RFID:</p>
                        <p class="font-mono text-sm text-foreground" x-text="selectedStudent?.rfid_number"></p>
                    </div>
                    <div x-show="!selectedStudent?.rfid_number" class="mt-2">
                        <p class="text-xs text-yellow-600">No RFID card registered</p>
                    </div>
                </div>
            </div>

            <!-- Status Messages -->
            <div class="mb-6 min-h-[24px]">
                <div x-show="scannerStatus === 'ready'" class="text-muted-foreground">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Ready to scan RFID card
                </div>
                <div x-show="scannerStatus === 'info'" class="text-blue-600">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="infoMessage"></span>
                </div>
                <div x-show="scannerStatus === 'scanning'" class="text-primary animate-pulse">
                    <svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing registration...
                </div>
                <div x-show="scannerStatus === 'success'" class="text-green-600">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span x-text="successMessage"></span>
                </div>
                <div x-show="scannerStatus === 'error'" class="text-red-600">
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
                        @keyup.enter="registerRfid()"
                        @input="handleRfidInput()"
                        placeholder="Tap RFID card or enter number"
                        class="w-full px-4 py-3 text-center text-lg bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground"
                        :disabled="!selectedStudentId || scannerStatus === 'scanning'"
                        x-ref="rfidInput">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg x-show="scannerStatus === 'scanning'" class="w-5 h-5 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 justify-center">
                <button 
                    @click="registerRfid()"
                    :disabled="!selectedStudentId || !rfidInput || scannerStatus === 'scanning'"
                    class="px-6 py-2 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Register Card
                </button>
                <button 
                    @click="clearInput()"
                    class="px-4 py-2 bg-secondary text-secondary-foreground rounded-lg font-medium hover:bg-secondary/80 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16m0-16L4 20"></path>
                    </svg>
                    Clear
                </button>
            </div>
            
            <!-- Quick Unregister for Selected Student -->
            <div x-show="selectedStudent?.rfid_number" class="mt-4 pt-4 border-t border-border">
                <div class="text-center">
                    <p class="text-sm text-muted-foreground mb-3">
                        <span x-text="selectedStudent?.name"></span> has RFID: 
                        <span class="font-mono text-foreground" x-text="selectedStudent?.rfid_number"></span>
                    </p>
                    <button 
                        @click="unregisterRfid()"
                        :disabled="scannerStatus === 'scanning'"
                        class="px-6 py-2 bg-destructive text-destructive-foreground rounded-lg font-medium hover:bg-destructive/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center mx-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Unregister RFID Card
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Students List Card -->
        <div class="bg-card rounded-lg border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-muted/30">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-foreground flex items-center">
                            <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Students & RFID Cards
                        </h3>
                        <p class="text-sm text-muted-foreground">Click to select a student</p>
                    </div>
                </div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="px-6 py-3 border-b border-border bg-muted/20">
                <div class="flex flex-col gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <input 
                            type="text" 
                            x-model="searchQuery"
                            @input="filterStudents()"
                            placeholder="Search students by name, course, or ID..."
                            class="w-full px-4 py-2 pl-10 pr-10 bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring focus:border-input transition-colors text-foreground placeholder:text-muted-foreground text-sm">
                        <svg class="w-4 h-4 absolute left-3 top-3 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <button 
                            x-show="searchQuery"
                            @click="clearSearch()"
                            class="absolute right-2 top-2 p-1 text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Filter Tabs -->
                    <div class="flex gap-2">
                        <button 
                            @click="setFilter('all')"
                            :class="currentFilter === 'all' ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                            All Students
                            <span class="ml-1 text-xs opacity-75" x-text="`(${getFilteredCount('all')})`"></span>
                        </button>
                        <button 
                            @click="setFilter('registered')"
                            :class="currentFilter === 'registered' ? 'bg-green-600 text-white' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                            Registered Only
                            <span class="ml-1 text-xs opacity-75" x-text="`(${getFilteredCount('registered')})`"></span>
                        </button>
                        <button 
                            @click="setFilter('unregistered')"
                            :class="currentFilter === 'unregistered' ? 'bg-yellow-600 text-white' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                            Unregistered Only
                            <span class="ml-1 text-xs opacity-75" x-text="`(${getFilteredCount('unregistered')})`"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto scrollbar-yellow">
                <template x-for="student in finalFilteredStudents" :key="student.id">
                    <div class="px-6 py-4 border-b border-border hover:bg-muted/50 transition-all duration-200"
                         :class="selectedStudentId == student.id ? 'bg-primary/10 border-l-4 border-l-primary' : ''">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 cursor-pointer" @click="selectStudent(student.id)">
                                <div class="flex items-center">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-foreground" x-text="student.name"></p>
                                        <p class="text-xs text-muted-foreground" x-text="student.course"></p>
                                        <p class="text-xs text-muted-foreground">ID: <span x-text="student.student_id"></span></p>
                                        <template x-if="student.rfid_number">
                                            <p class="text-xs font-mono text-green-600 mt-1" x-text="`RFID: ${student.rfid_number}`"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="ml-4 flex items-center gap-2">
                                <!-- Status Badge -->
                                <div class="text-right">
                                    <template x-if="student.rfid_number">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Registered
                                        </span>
                                    </template>
                                    <template x-if="!student.rfid_number">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Pending
                                        </span>
                                    </template>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex gap-1">
                                    <template x-if="!student.rfid_number">
                                        <button 
                                            @click.stop="quickRegister(student)"
                                            :disabled="scannerStatus === 'scanning'"
                                            class="px-3 py-1 bg-primary text-primary-foreground rounded-md text-xs font-medium hover:bg-primary/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Register RFID Card">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="student.rfid_number">
                                        <button 
                                            @click.stop="quickUnregister(student)"
                                            :disabled="scannerStatus === 'scanning'"
                                            class="px-3 py-1 bg-destructive text-destructive-foreground rounded-md text-xs font-medium hover:bg-destructive/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            title="Unregister RFID Card">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </template>
                                    <button 
                                        @click.stop="selectStudent(student.id)"
                                        :class="selectedStudentId == student.id ? 'bg-primary text-primary-foreground' : 'bg-secondary text-secondary-foreground hover:bg-secondary/80'"
                                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                                        title="Select Student">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="finalFilteredStudents.length === 0" class="px-6 py-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-muted-foreground/50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-muted-foreground">
                        <template x-if="searchQuery">
                            <span>No students found matching "<span x-text="searchQuery"></span>" in <span x-text="currentFilter === 'all' ? 'all students' : currentFilter + ' students'"></span></span>
                        </template>
                        <template x-if="!searchQuery">
                            <span>No students found for the selected filter</span>
                        </template>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Stats Card -->
        <div class="bg-card rounded-lg border border-border p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center">
                <svg class="w-5 h-5 text-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Registration Statistics
            </h3>
            
            <div class="space-y-4">
                <!-- Progress Bar -->
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="text-muted-foreground">Registration Progress</span>
                        <span class="font-medium text-foreground">{{ number_format(($registeredCount / $totalStudents) * 100, 1) }}%</span>
                    </div>
                    <div class="w-full bg-muted rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300" 
                             style="width: {{ ($registeredCount / $totalStudents) * 100 }}%"></div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-2xl font-bold text-green-600">{{ $registeredCount }}</div>
                        <div class="text-sm text-green-700">Registered</div>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="text-2xl font-bold text-yellow-600">{{ $totalStudents - $registeredCount }}</div>
                        <div class="text-sm text-yellow-700">Pending</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-foreground mb-3">Recent Registrations</h4>
                    <div class="space-y-2">
                        @foreach($students->where('rfid_number', '!=', null)->sortByDesc('updated_at')->take(3) as $student)
                        <div class="flex items-center text-sm">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-foreground">{{ $student->name }}</span>
                            <span class="text-muted-foreground ml-auto">{{ $student->updated_at->diffForHumans() }}</span>
                        </div>
                        @endforeach
                        @if($students->where('rfid_number', '!=', null)->count() === 0)
                        <div class="text-sm text-muted-foreground text-center py-2">
                            No registrations yet
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function rfidRegistration() {
    return {
        selectedStudentId: '',
        selectedStudent: null,
        rfidInput: '',
        scannerStatus: 'ready', // ready, info, scanning, success, error
        successMessage: '',
        errorMessage: '',
        infoMessage: '',
        currentFilter: 'all',
        searchQuery: '',
        students: @json($students),

        get filteredStudents() {
            if (this.currentFilter === 'registered') {
                return this.students.filter(student => student.rfid_number);
            } else if (this.currentFilter === 'unregistered') {
                return this.students.filter(student => !student.rfid_number);
            }
            return this.students;
        },

        get finalFilteredStudents() {
            let filtered = this.filteredStudents;
            
            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(student => 
                    student.name.toLowerCase().includes(query) ||
                    student.course.toLowerCase().includes(query) ||
                    student.student_id.toLowerCase().includes(query)
                );
            }
            
            return filtered;
        },

        get searchFilteredStudents() {
            // Keep this for backward compatibility with the select dropdown
            return this.finalFilteredStudents;
        },

        init() {
            // Auto-focus the RFID input when a student is selected
            this.$watch('selectedStudentId', () => {
                if (this.selectedStudentId) {
                    this.$nextTick(() => {
                        this.$refs.rfidInput.focus();
                    });
                }
            });
        },

        setFilter(filter) {
            this.currentFilter = filter;
        },

        getFilteredCount(filter) {
            let baseStudents;
            if (filter === 'registered') {
                baseStudents = this.students.filter(student => student.rfid_number);
            } else if (filter === 'unregistered') {
                baseStudents = this.students.filter(student => !student.rfid_number);
            } else {
                baseStudents = this.students;
            }

            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase();
                baseStudents = baseStudents.filter(student => 
                    student.name.toLowerCase().includes(query) ||
                    student.course.toLowerCase().includes(query) ||
                    student.student_id.toLowerCase().includes(query)
                );
            }

            return baseStudents.length;
        },

        clearSearch() {
            this.searchQuery = '';
        },

        filterStudents() {
            // This method is called when search input changes
            // The computed property searchFilteredStudents will automatically update
        },

        onStudentChange() {
            if (this.selectedStudentId) {
                // Find the student from the students array
                const student = this.students.find(s => s.id == this.selectedStudentId);
                if (student) {
                    this.selectedStudent = {
                        id: student.id,
                        name: student.name,
                        course: student.course,
                        rfid_number: student.rfid_number || null
                    };
                    this.clearInput();
                }
            } else {
                this.selectedStudent = null;
            }
        },

        selectStudent(studentId) {
            this.selectedStudentId = studentId.toString();
            this.onStudentChange();
        },

        async quickRegister(student) {
            // Auto-select the student and focus on RFID input
            this.selectedStudentId = student.id.toString();
            this.onStudentChange();
            
            // Focus on RFID input and show a helpful message
            this.$nextTick(() => {
                this.$refs.rfidInput.focus();
                this.showInfo(`Selected ${student.name}. Please tap or enter RFID card number.`);
            });
        },

        async quickUnregister(student) {
            if (!student.rfid_number) {
                this.showError('Student does not have an RFID card registered');
                return;
            }

            if (!confirm(`Are you sure you want to unregister the RFID card from ${student.name}?\n\nRFID: ${student.rfid_number}`)) {
                return;
            }

            this.scannerStatus = 'scanning';
            
            try {
                const response = await fetch('/admin/rfid-registration/unregister', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        student_id: student.id
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess(data.message);
                    
                    // Update the student in the local array
                    const studentIndex = this.students.findIndex(s => s.id == student.id);
                    if (studentIndex !== -1) {
                        this.students[studentIndex].rfid_number = null;
                        this.students[studentIndex].updated_at = new Date().toISOString();
                    }

                    // Update selected student if it's the same one
                    if (this.selectedStudentId == student.id) {
                        this.selectedStudent.rfid_number = null;
                    }
                    
                    // Refresh the page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.showError(data.message || 'Failed to unregister RFID card');
                }
            } catch (error) {
                console.error('RFID unregistration error:', error);
                this.showError('Network error. Please try again.');
            }
        },

        handleRfidInput() {
            // Reset status when user starts typing
            if (this.scannerStatus === 'success' || this.scannerStatus === 'error' || this.scannerStatus === 'info') {
                this.scannerStatus = 'ready';
            }
        },

        async registerRfid() {
            if (!this.selectedStudentId) {
                this.showError('Please select a student first');
                return;
            }

            if (!this.rfidInput.trim()) {
                this.showError('Please enter or scan an RFID number');
                return;
            }

            this.scannerStatus = 'scanning';
            
            try {
                const response = await fetch('/admin/rfid-registration/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        student_id: this.selectedStudentId,
                        rfid_number: this.rfidInput.trim()
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess(data.message);
                    this.selectedStudent.rfid_number = data.student.rfid_number;
                    
                    // Update the student in the local array
                    const studentIndex = this.students.findIndex(s => s.id == this.selectedStudentId);
                    if (studentIndex !== -1) {
                        this.students[studentIndex].rfid_number = data.student.rfid_number;
                        this.students[studentIndex].updated_at = new Date().toISOString();
                    }
                    
                    this.clearInput();
                    
                    // Refresh the page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.showError(data.message || 'Failed to register RFID card');
                }
            } catch (error) {
                console.error('RFID registration error:', error);
                this.showError('Network error. Please try again.');
            }
        },

        async unregisterRfid() {
            if (!this.selectedStudentId) {
                this.showError('Please select a student first');
                return;
            }

            if (!this.selectedStudent?.rfid_number) {
                this.showError('Student does not have an RFID card registered');
                return;
            }

            if (!confirm(`Are you sure you want to unregister the RFID card from ${this.selectedStudent.name}?`)) {
                return;
            }

            this.scannerStatus = 'scanning';
            
            try {
                const response = await fetch('/admin/rfid-registration/unregister', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        student_id: this.selectedStudentId
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.showSuccess(data.message);
                    this.selectedStudent.rfid_number = null;
                    
                    // Update the student in the local array
                    const studentIndex = this.students.findIndex(s => s.id == this.selectedStudentId);
                    if (studentIndex !== -1) {
                        this.students[studentIndex].rfid_number = null;
                        this.students[studentIndex].updated_at = new Date().toISOString();
                    }
                    
                    // Refresh the page after a short delay to show updated data
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.showError(data.message || 'Failed to unregister RFID card');
                }
            } catch (error) {
                console.error('RFID unregistration error:', error);
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

        showInfo(message) {
            this.scannerStatus = 'info';
            this.infoMessage = message;
            setTimeout(() => {
                if (this.scannerStatus === 'info') {
                    this.scannerStatus = 'ready';
                }
            }, 4000);
        },

        clearInput() {
            this.rfidInput = '';
            if (this.$refs.rfidInput) {
                this.$refs.rfidInput.focus();
            }
        }
    }
}
</script>
@endsection