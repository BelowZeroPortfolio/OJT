<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name', 'OJT Attendance System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground antialiased">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center mb-6">
                <div class="w-12 h-12 border-2 border-primary rounded-lg flex items-center justify-center">
                    <svg class="h-7 w-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-center text-2xl font-bold text-foreground">
                OJT Attendance System
            </h1>
            <h2 class="mt-6 text-center text-3xl font-bold text-foreground">
                Sign in to your account
            </h2>
            <p class="mt-3 text-center text-sm text-muted-foreground">
                Enter your credentials to access the system
            </p>
        </div>

        <!-- Login Form -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-card py-8 px-4 border border-border rounded-lg sm:px-10">
                <form method="POST" action="{{ route('login') }}" 
                      class="space-y-6"
                      x-data="{
                          email: '',
                          password: '',
                          emailError: '',
                          passwordError: '',
                          isSubmitting: false,
                          validateEmail() {
                              if (!this.email) {
                                  this.emailError = 'Email is required';
                                  return false;
                              }
                              const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                              if (!emailRegex.test(this.email)) {
                                  this.emailError = 'Please enter a valid email address';
                                  return false;
                              }
                              this.emailError = '';
                              return true;
                          },
                          validatePassword() {
                              if (!this.password) {
                                  this.passwordError = 'Password is required';
                                  return false;
                              }
                              if (this.password.length < 6) {
                                  this.passwordError = 'Password must be at least 6 characters';
                                  return false;
                              }
                              this.passwordError = '';
                              return true;
                          },
                          validateForm() {
                              const emailValid = this.validateEmail();
                              const passwordValid = this.validatePassword();
                              return emailValid && passwordValid;
                          },
                          handleSubmit() {
                              if (this.validateForm()) {
                                  this.isSubmitting = true;
                                  return true;
                              }
                              return false;
                          }
                      }"
                      @submit="if (!handleSubmit()) { $event.preventDefault(); }">
                    @csrf

                    <!-- Server-side Errors -->
                    @if ($errors->any())
                        <div class="rounded-lg bg-destructive/10 border border-destructive/50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-destructive" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-destructive">
                                        There were errors with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-destructive-foreground">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-foreground mb-2">
                            Email address
                        </label>
                        <div>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   autocomplete="email" 
                                   required
                                   x-model="email"
                                   @blur="validateEmail()"
                                   @input="emailError = ''"
                                   :class="emailError ? 'border-destructive focus:border-destructive focus:ring-destructive' : 'border-input focus:border-ring focus:ring-ring'"
                                   class="appearance-none block w-full px-3 py-2.5 bg-background border rounded-lg text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 text-sm transition-all"
                                   placeholder="you@example.com"
                                   value="{{ old('email') }}">
                        </div>
                        <p x-show="emailError" 
                           x-text="emailError" 
                           class="mt-1.5 text-sm text-destructive"
                           style="display: none;"></p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-foreground mb-2">
                            Password
                        </label>
                        <div>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password" 
                                   required
                                   x-model="password"
                                   @blur="validatePassword()"
                                   @input="passwordError = ''"
                                   :class="passwordError ? 'border-destructive focus:border-destructive focus:ring-destructive' : 'border-input focus:border-ring focus:ring-ring'"
                                   class="appearance-none block w-full px-3 py-2.5 bg-background border rounded-lg text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 text-sm transition-all"
                                   placeholder="••••••••">
                        </div>
                        <p x-show="passwordError" 
                           x-text="passwordError" 
                           class="mt-1.5 text-sm text-destructive"
                           style="display: none;"></p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" 
                                   name="remember" 
                                   type="checkbox"
                                   class="h-4 w-4 text-primary focus:ring-primary border-input rounded bg-background">
                            <label for="remember" class="ml-2 block text-sm text-muted-foreground">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                :disabled="isSubmitting"
                                :class="isSubmitting ? 'bg-primary/50 cursor-not-allowed' : 'bg-primary hover:bg-primary/90'"
                                class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg text-sm font-medium text-primary-foreground focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all shadow-lg">
                            <span x-show="!isSubmitting">Sign in</span>
                            <span x-show="isSubmitting" class="flex items-center" style="display: none;">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Signing in...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Additional Info -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-border"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-card text-muted-foreground">
                                Need help?
                            </span>
                        </div>
                    </div>
                    <div class="mt-6 text-center">
                        <p class="text-sm text-muted-foreground">
                            Contact your administrator for account assistance
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
