<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration - OJT Attendance System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-card border-b border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="{{ route('welcome') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 border-2 border-primary rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold text-foreground">OJT Attendance System</span>
                    </a>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-muted-foreground">Already have an account?</span>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-border hover:bg-accent hover:text-accent-foreground transition-colors">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Registration Form -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-card border border-border rounded-lg p-6 lg:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl lg:text-3xl font-bold text-foreground mb-2">Student Registration</h1>
                    <p class="text-sm text-muted-foreground">Create your account to start tracking your OJT attendance</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-destructive/10 border border-destructive/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-destructive mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-destructive mb-2">Please correct the following errors:</h3>
                                <ul class="list-disc list-inside text-sm text-destructive/90 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" x-data="registrationForm()" @submit="validateForm">
                    @csrf

                    <!-- Two Column Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-5">
                        <!-- Left Column -->
                        <div class="space-y-5">
                            <!-- Full Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-foreground mb-1.5">
                                    Full Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}"
                                    required
                                    x-model="name"
                                    @input="validateName"
                                    class="w-full px-3 py-2 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('name') border-destructive @enderror"
                                    :class="{ 'border-destructive': nameError, 'border-green-500': nameValid }"
                                    placeholder="Juan Dela Cruz"
                                >
                                <p x-show="nameError" x-text="nameError" class="mt-1 text-xs text-destructive"></p>
                                @error('name')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Student ID -->
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-foreground mb-1.5">
                                    Student ID <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="student_id" 
                                    name="student_id" 
                                    value="{{ old('student_id') }}"
                                    required
                                    x-model="studentId"
                                    @input="validateStudentId"
                                    class="w-full px-3 py-2 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('student_id') border-destructive @enderror"
                                    :class="{ 'border-destructive': studentIdError, 'border-green-500': studentIdValid }"
                                    placeholder="2024-12345"
                                >
                                <p x-show="studentIdError" x-text="studentIdError" class="mt-1 text-xs text-destructive"></p>
                                @error('student_id')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-foreground mb-1.5">
                                    Email Address <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    required
                                    x-model="email"
                                    @input="validateEmail"
                                    class="w-full px-3 py-2 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('email') border-destructive @enderror"
                                    :class="{ 'border-destructive': emailError, 'border-green-500': emailValid }"
                                    placeholder="student@example.com"
                                >
                                <p x-show="emailError" x-text="emailError" class="mt-1 text-xs text-destructive"></p>
                                @error('email')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Course -->
                            <div>
                                <label for="course" class="block text-sm font-medium text-foreground mb-1.5">
                                    Course <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="course" 
                                    name="course" 
                                    value="{{ old('course') }}"
                                    required
                                    x-model="course"
                                    @input="validateCourse"
                                    class="w-full px-3 py-2 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('course') border-destructive @enderror"
                                    :class="{ 'border-destructive': courseError, 'border-green-500': courseValid }"
                                    placeholder="BS Computer Science"
                                >
                                <p x-show="courseError" x-text="courseError" class="mt-1 text-xs text-destructive"></p>
                                @error('course')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-5">
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-foreground mb-1.5">
                                    Password <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        :type="showPassword ? 'text' : 'password'"
                                        id="password" 
                                        name="password" 
                                        required
                                        minlength="8"
                                        x-model="password"
                                        @input="validatePassword"
                                        class="w-full px-3 py-2 pr-10 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('password') border-destructive @enderror"
                                        :class="{ 'border-destructive': passwordError, 'border-green-500': passwordValid }"
                                        placeholder="Minimum 8 characters"
                                    >
                                    <button 
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    >
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                        </svg>
                                    </button>
                                </div>
                                <!-- Password Strength Indicator -->
                                <div x-show="password.length > 0" class="mt-2 space-y-1">
                                    <div class="flex gap-1">
                                        <div class="h-1 flex-1 rounded-full" :class="passwordStrength >= 1 ? (passwordStrength >= 3 ? 'bg-green-500' : 'bg-yellow-500') : 'bg-gray-300'"></div>
                                        <div class="h-1 flex-1 rounded-full" :class="passwordStrength >= 2 ? (passwordStrength >= 3 ? 'bg-green-500' : 'bg-yellow-500') : 'bg-gray-300'"></div>
                                        <div class="h-1 flex-1 rounded-full" :class="passwordStrength >= 3 ? 'bg-green-500' : 'bg-gray-300'"></div>
                                    </div>
                                    <p class="text-xs" :class="passwordStrength >= 3 ? 'text-green-600' : (passwordStrength >= 2 ? 'text-yellow-600' : 'text-destructive')" x-text="passwordStrengthText"></p>
                                </div>
                                <p x-show="passwordError" x-text="passwordError" class="mt-1 text-xs text-destructive"></p>
                                @error('password')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-foreground mb-1.5">
                                    Confirm Password <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        :type="showPasswordConfirm ? 'text' : 'password'"
                                        id="password_confirmation" 
                                        name="password_confirmation" 
                                        required
                                        minlength="8"
                                        x-model="passwordConfirmation"
                                        @input="validatePasswordConfirmation"
                                        class="w-full px-3 py-2 pr-10 bg-background border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm"
                                        :class="{ 'border-destructive': passwordConfirmError, 'border-green-500': passwordConfirmValid }"
                                        placeholder="Re-enter your password"
                                    >
                                    <button 
                                        type="button"
                                        @click="showPasswordConfirm = !showPasswordConfirm"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    >
                                        <svg x-show="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <svg x-show="showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p x-show="passwordConfirmError" x-text="passwordConfirmError" class="mt-1 text-xs text-destructive"></p>
                                <p x-show="passwordConfirmValid" class="mt-1 text-xs text-green-600">✓ Passwords match</p>
                            </div>

                            <!-- OJT Location -->
                            <div>
                                <label for="assigned_location_id" class="block text-sm font-medium text-foreground mb-1.5">
                                    OJT Training Site <span class="text-destructive">*</span>
                                </label>
                                <select 
                                    id="assigned_location_id" 
                                    name="assigned_location_id" 
                                    required
                                    x-model="selectedLocation"
                                    class="w-full px-3 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('assigned_location_id') border-destructive @enderror"
                                >
                                    <option value="">Select your OJT site</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ old('assigned_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                    <option value="other" {{ old('assigned_location_id') == 'other' ? 'selected' : '' }}>
                                        Other (New OJT Site)
                                    </option>
                                </select>
                                @error('assigned_location_id')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- New Location Fields (Full Width) -->
                    <div x-show="selectedLocation === 'other'" x-transition class="mt-5 space-y-4 p-4 bg-secondary/50 border border-border rounded-lg">
                        <p class="text-sm text-muted-foreground flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Your new OJT site will require admin approval before activation.</span>
                        </p>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label for="new_location_name" class="block text-sm font-medium text-foreground mb-1.5">
                                    OJT Site Name <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="new_location_name" 
                                    name="new_location_name" 
                                    value="{{ old('new_location_name') }}"
                                    x-bind:required="selectedLocation === 'other'"
                                    class="w-full px-3 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('new_location_name') border-destructive @enderror"
                                    placeholder="Company Name or Organization"
                                >
                                @error('new_location_name')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_location_address" class="block text-sm font-medium text-foreground mb-1.5">
                                    OJT Site Address <span class="text-destructive">*</span>
                                </label>
                                <input 
                                    type="text"
                                    id="new_location_address" 
                                    name="new_location_address" 
                                    value="{{ old('new_location_address') }}"
                                    x-bind:required="selectedLocation === 'other'"
                                    class="w-full px-3 py-2 bg-background border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm @error('new_location_address') border-destructive @enderror"
                                    placeholder="Complete address of the OJT site"
                                >
                                @error('new_location_address')
                                    <p class="mt-1 text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button 
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!isFormValid"
                        >
                            Create Account
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <p class="text-center text-xs text-muted-foreground mt-4">
                By registering, you agree to our Terms of Service and Privacy Policy
            </p>
        </div>
    </div>

    <script>
        function registrationForm() {
            return {
                // Form fields
                name: '{{ old("name", "") }}',
                studentId: '{{ old("student_id", "") }}',
                email: '{{ old("email", "") }}',
                course: '{{ old("course", "") }}',
                password: '',
                passwordConfirmation: '',
                selectedLocation: '{{ old("assigned_location_id", "") }}',
                
                // Validation states
                nameError: '',
                nameValid: false,
                studentIdError: '',
                studentIdValid: false,
                emailError: '',
                emailValid: false,
                courseError: '',
                courseValid: false,
                passwordError: '',
                passwordValid: false,
                passwordConfirmError: '',
                passwordConfirmValid: false,
                
                // Password visibility
                showPassword: false,
                showPasswordConfirm: false,
                
                // Password strength
                passwordStrength: 0,
                passwordStrengthText: '',
                
                // Computed property for form validity
                get isFormValid() {
                    return this.nameValid && 
                           this.studentIdValid && 
                           this.emailValid && 
                           this.courseValid && 
                           this.passwordValid && 
                           this.passwordConfirmValid &&
                           this.selectedLocation !== '';
                },
                
                // Validation methods
                validateName() {
                    const value = this.name.trim();
                    if (!value) {
                        this.nameError = 'Full name is required';
                        this.nameValid = false;
                    } else if (value.length < 2) {
                        this.nameError = 'Name must be at least 2 characters';
                        this.nameValid = false;
                    } else if (!/^[a-zA-Z\s\.\-]+$/.test(value)) {
                        this.nameError = 'Name can only contain letters, spaces, dots, and hyphens';
                        this.nameValid = false;
                    } else {
                        this.nameError = '';
                        this.nameValid = true;
                    }
                },
                
                validateStudentId() {
                    const value = this.studentId.trim();
                    if (!value) {
                        this.studentIdError = 'Student ID is required';
                        this.studentIdValid = false;
                    } else if (value.length < 3) {
                        this.studentIdError = 'Student ID must be at least 3 characters';
                        this.studentIdValid = false;
                    } else if (!/^[a-zA-Z0-9\-]+$/.test(value)) {
                        this.studentIdError = 'Student ID can only contain letters, numbers, and hyphens';
                        this.studentIdValid = false;
                    } else {
                        this.studentIdError = '';
                        this.studentIdValid = true;
                    }
                },
                
                validateEmail() {
                    const value = this.email.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (!value) {
                        this.emailError = 'Email address is required';
                        this.emailValid = false;
                    } else if (!emailRegex.test(value)) {
                        this.emailError = 'Please enter a valid email address';
                        this.emailValid = false;
                    } else {
                        this.emailError = '';
                        this.emailValid = true;
                    }
                },
                
                validateCourse() {
                    const value = this.course.trim();
                    if (!value) {
                        this.courseError = 'Course is required';
                        this.courseValid = false;
                    } else if (value.length < 2) {
                        this.courseError = 'Course must be at least 2 characters';
                        this.courseValid = false;
                    } else {
                        this.courseError = '';
                        this.courseValid = true;
                    }
                },
                
                validatePassword() {
                    const value = this.password;
                    let strength = 0;
                    
                    if (!value) {
                        this.passwordError = 'Password is required';
                        this.passwordValid = false;
                        this.passwordStrength = 0;
                        this.passwordStrengthText = '';
                        return;
                    }
                    
                    if (value.length < 8) {
                        this.passwordError = 'Password must be at least 8 characters';
                        this.passwordValid = false;
                        this.passwordStrength = 0;
                        this.passwordStrengthText = 'Too short';
                        return;
                    }
                    
                    // Calculate password strength
                    if (value.length >= 8) strength++;
                    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) strength++;
                    if (/\d/.test(value)) strength++;
                    if (/[^a-zA-Z0-9]/.test(value)) strength++;
                    
                    this.passwordStrength = Math.min(strength, 3);
                    
                    // Check for letters and numbers
                    const hasLetter = /[a-zA-Z]/.test(value);
                    const hasNumber = /\d/.test(value);
                    
                    if (!hasLetter || !hasNumber) {
                        this.passwordError = 'Password must contain both letters and numbers';
                        this.passwordValid = false;
                        this.passwordStrengthText = 'Weak - Add letters and numbers';
                    } else {
                        this.passwordError = '';
                        this.passwordValid = true;
                        
                        if (this.passwordStrength === 1) {
                            this.passwordStrengthText = 'Weak';
                        } else if (this.passwordStrength === 2) {
                            this.passwordStrengthText = 'Medium';
                        } else {
                            this.passwordStrengthText = 'Strong';
                        }
                    }
                    
                    // Re-validate confirmation if it exists
                    if (this.passwordConfirmation) {
                        this.validatePasswordConfirmation();
                    }
                },
                
                validatePasswordConfirmation() {
                    const value = this.passwordConfirmation;
                    
                    if (!value) {
                        this.passwordConfirmError = 'Please confirm your password';
                        this.passwordConfirmValid = false;
                    } else if (value !== this.password) {
                        this.passwordConfirmError = 'Passwords do not match';
                        this.passwordConfirmValid = false;
                    } else {
                        this.passwordConfirmError = '';
                        this.passwordConfirmValid = true;
                    }
                },
                
                validateForm(e) {
                    // Validate all fields before submission
                    this.validateName();
                    this.validateStudentId();
                    this.validateEmail();
                    this.validateCourse();
                    this.validatePassword();
                    this.validatePasswordConfirmation();
                    
                    if (!this.isFormValid) {
                        e.preventDefault();
                        
                        // Scroll to first error
                        const firstError = document.querySelector('.border-destructive');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstError.focus();
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
