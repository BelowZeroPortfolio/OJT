<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OJT Monitoring & Attendance Tracking System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-card border-b border-border">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 border-2 border-primary rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold text-foreground">OJT Attendance System</span>
                    </div>
                    <div>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-colors">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-32">
            <div class="text-center">
                <div class="inline-block mb-8">
                    <div class="px-4 py-2 border border-border rounded-lg bg-secondary">
                        <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Modern Attendance System</span>
                    </div>
                </div>
                <h1 class="text-5xl sm:text-6xl md:text-7xl font-bold text-foreground tracking-tight leading-tight">
                    <span class="block">OJT Monitoring &</span>
                    <span class="block text-primary mt-3">Attendance Tracking</span>
                </h1>
                <p class="mt-8 max-w-2xl mx-auto text-xl text-muted-foreground leading-relaxed">
                    Streamline attendance tracking for students at multiple on-the-job training locations with RFID-enabled monitoring and real-time dashboards.
                </p>
                <div class="mt-12 flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-all shadow-lg hover:shadow-xl">
                        Get Started
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                    <a href="#features" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-medium rounded-lg bg-secondary text-foreground border border-border hover:bg-accent transition-all">
                        Learn More
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Features Section -->
            <div id="features" class="mt-32">
                <div class="text-center mb-12">
                    <h2 class="text-3xl sm:text-4xl font-bold text-foreground mb-4">
                        Powerful Features
                    </h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Everything you need to manage attendance tracking efficiently
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">RFID Integration</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">Automatic attendance logging with RFID scanner hardware for seamless check-in and check-out.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">Real-Time Dashboards</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">Role-based dashboards for students and administrators with live attendance updates.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">Report Generation</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">Generate daily, weekly, and monthly attendance reports in CSV or PDF format.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">Multi-Location Support</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">Manage students across multiple training locations with location-based validation.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">Secure & Reliable</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">JWT authentication, role-based access control, and rate limiting for security.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-card rounded-lg border border-border p-6 hover:border-primary/50 hover:shadow-md transition-all group">
                        <div class="flex items-center justify-center h-12 w-12 rounded-lg border-2 border-border group-hover:border-primary transition-colors mb-5">
                            <svg class="h-6 w-6 text-foreground group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-foreground mb-2.5">High Performance</h3>
                        <p class="text-sm text-muted-foreground leading-relaxed">Optimized database queries, caching, and asynchronous job processing for speed.</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="mt-32 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-card rounded-lg border border-border p-6 text-center">
                    <div class="text-4xl font-bold text-primary mb-2">100%</div>
                    <div class="text-sm text-muted-foreground">Accurate Tracking</div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 text-center">
                    <div class="text-4xl font-bold text-primary mb-2">Real-Time</div>
                    <div class="text-sm text-muted-foreground">Live Updates</div>
                </div>
                <div class="bg-card rounded-lg border border-border p-6 text-center">
                    <div class="text-4xl font-bold text-primary mb-2">24/7</div>
                    <div class="text-sm text-muted-foreground">System Availability</div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mt-32 bg-card border border-border rounded-lg overflow-hidden relative">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent"></div>
                <div class="relative px-6 py-16 sm:px-12 sm:py-20 text-center">
                    <h2 class="text-3xl sm:text-4xl font-bold text-foreground mb-4">
                        Ready to get started?
                    </h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-8">
                        Sign in to access your dashboard and start tracking attendance with our modern, efficient system.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 text-base font-medium rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 transition-all shadow-lg hover:shadow-xl">
                            Sign In Now
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-card border-t border-border mt-32">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 border-2 border-primary rounded-lg flex items-center justify-center">
                                <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-base font-semibold text-foreground">OJT System</span>
                        </div>
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Modern attendance tracking solution for on-the-job training programs.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-4">Features</h3>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>RFID Integration</li>
                            <li>Real-Time Tracking</li>
                            <li>Report Generation</li>
                            <li>Multi-Location</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-foreground mb-4">Technology</h3>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>Laravel Framework</li>
                            <li>Tailwind CSS</li>
                            <li>Alpine.js</li>
                            <li>MySQL Database</li>
                        </ul>
                    </div>
                </div>
                <div class="pt-8 border-t border-border text-center text-sm text-muted-foreground">
                    <p>&copy; {{ date('Y') }} OJT Attendance System. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
