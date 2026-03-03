<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OJT Attendance System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-background antialiased">
    <div class="min-h-screen" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
        <!-- Sidebar -->
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'" 
            class="fixed inset-y-0 left-0 z-50 bg-sidebar border-r border-sidebar-foreground/20 transition-all duration-300 ease-in-out hidden lg:block overflow-visible">
            
            <!-- Logo Section -->
            <div class="flex items-center h-16 px-4 border-b border-border" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
                <div x-show="sidebarOpen" class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-foreground whitespace-nowrap">OJT System</span>
                </div>
                <button 
                    @click="sidebarOpen = !sidebarOpen"
                    :class="sidebarOpen ? '' : 'mx-auto'"
                    class="p-2 rounded-lg hover:bg-accent text-muted-foreground hover:text-foreground transition-all duration-200"
                    :title="sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'">
                    <svg x-show="sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <svg x-show="!sidebarOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto overflow-x-visible">
                <!-- Dashboard -->
                <div class="relative group">
                    <a href="{{ route('admin.dashboard') }}" 
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-primary-foreground' : 'text-sidebar-foreground/60 hover:bg-sidebar-foreground/10 hover:text-sidebar-foreground' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium whitespace-nowrap">Dashboard</span>
                    </a>
                    <div x-show="!sidebarOpen" 
                         class="fixed left-20 px-3 py-2 bg-card text-foreground text-sm rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50 border border-border"
                         style="margin-top: -2.25rem;">
                        Dashboard
                    </div>
                </div>

                <!-- Students -->
                <div class="relative group">
                    <a href="{{ route('admin.users.index') }}" 
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-primary text-primary-foreground' : 'text-sidebar-foreground/60 hover:bg-sidebar-foreground/10 hover:text-sidebar-foreground' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium whitespace-nowrap">Students</span>
                    </a>
                    <div x-show="!sidebarOpen" 
                         class="fixed left-20 px-3 py-2 bg-card text-foreground text-sm rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50 border border-border"
                         style="margin-top: -2.25rem;">
                        Students
                    </div>
                </div>

                <!-- Locations -->
                <div class="relative group">
                    <a href="{{ route('admin.locations.index') }}" 
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.locations.*') ? 'bg-primary text-primary-foreground' : 'text-sidebar-foreground/60 hover:bg-sidebar-foreground/10 hover:text-sidebar-foreground' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium whitespace-nowrap">Locations</span>
                    </a>
                    <div x-show="!sidebarOpen" 
                         class="fixed left-20 px-3 py-2 bg-card text-foreground text-sm rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50 border border-border"
                         style="margin-top: -2.25rem;">
                        Locations
                    </div>
                </div>

                <!-- Divider -->
                <div x-show="sidebarOpen" x-transition class="pt-4 pb-2">
                    <div class="px-3 text-xs font-semibold text-sidebar-foreground/40 uppercase tracking-wider">Reports</div>
                </div>
                <div x-show="!sidebarOpen" class="pt-4 pb-2">
                    <div class="h-px bg-sidebar-foreground/10 mx-3"></div>
                </div>

                <!-- Reports -->
                <div class="relative group">
                    <a href="{{ route('admin.reports') }}" 
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.reports') ? 'bg-primary text-primary-foreground' : 'text-sidebar-foreground/60 hover:bg-sidebar-foreground/10 hover:text-sidebar-foreground' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium whitespace-nowrap">Reports</span>
                    </a>
                    <div x-show="!sidebarOpen" 
                         class="fixed left-20 px-3 py-2 bg-card text-foreground text-sm rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50 border border-border"
                         style="margin-top: -2.25rem;">
                        Reports
                    </div>
                </div>

                <!-- Activity Logs -->
                <div class="relative group">
                    <a href="{{ route('admin.activity-logs') }}" 
                       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                       class="flex items-center px-3 py-2.5 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.activity-logs') ? 'bg-primary text-primary-foreground' : 'text-sidebar-foreground/60 hover:bg-sidebar-foreground/10 hover:text-sidebar-foreground' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium whitespace-nowrap">Activity Logs</span>
                    </a>
                    <div x-show="!sidebarOpen" 
                         class="fixed left-20 px-3 py-2 bg-card text-foreground text-sm rounded-lg whitespace-nowrap opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none shadow-lg z-50 border border-border"
                         style="margin-top: -2.25rem;">
                        Activity Logs
                    </div>
                </div>
            </nav>

            <!-- User Section -->
            <div class="border-t border-border p-3">
                <div class="flex items-center" :class="sidebarOpen ? 'space-x-3' : 'justify-center'">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                        <span class="text-sm font-semibold text-primary-foreground">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div x-show="sidebarOpen" x-transition class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-foreground truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3" x-show="sidebarOpen" x-transition>
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-foreground bg-secondary hover:bg-accent rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Sidebar -->
        <div 
            x-show="mobileMenuOpen"
            @click="mobileMenuOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            style="display: none;">
        </div>

        <aside 
            x-show="mobileMenuOpen"
            @click.away="mobileMenuOpen = false"
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-card border-r border-border lg:hidden"
            style="display: none;">
            
            <!-- Mobile Logo Section -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-border">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="text-lg font-semibold text-foreground">OJT System</span>
                </div>
                <button 
                    @click="mobileMenuOpen = false"
                    class="p-1.5 rounded-lg hover:bg-accent text-muted-foreground transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-3 font-medium">Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="ml-3 font-medium">Students</span>
                </a>

                <a href="{{ route('admin.locations.index') }}" 
                   class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.locations.*') ? 'bg-accent text-foreground' : 'text-muted-foreground hover:bg-accent hover:text-foreground' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="ml-3 font-medium">Locations</span>
                </a>

                <div class="pt-4 pb-2">
                    <div class="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Reports</div>
                </div>

                <a href="#" class="flex items-center px-3 py-2.5 rounded-lg transition-colors text-muted-foreground hover:bg-accent hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="ml-3 font-medium">Analytics</span>
                </a>

                <a href="#" class="flex items-center px-3 py-2.5 rounded-lg transition-colors text-muted-foreground hover:bg-accent hover:text-foreground">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="ml-3 font-medium">Activity Logs</span>
                </a>
            </nav>

            <!-- Mobile User Section -->
            <div class="border-t border-border p-3">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                        <span class="text-sm font-semibold text-primary-foreground">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-foreground truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-foreground bg-secondary hover:bg-accent rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" class="transition-all duration-300 ease-in-out">
            <!-- Top Bar -->
            <header class="sticky top-0 z-30 bg-card border-b border-border">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button 
                            @click="mobileMenuOpen = true"
                            class="lg:hidden p-2 rounded-lg hover:bg-accent text-muted-foreground transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        
                        <div>
                            <h1 class="text-xl font-semibold text-foreground">@yield('page-title', 'Dashboard')</h1>
                            <p class="text-sm text-muted-foreground hidden sm:block">@yield('page-description', 'Welcome back')</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <!-- Notifications -->
                        <button class="p-2 rounded-lg hover:bg-accent text-muted-foreground transition-colors relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-destructive rounded-full"></span>
                        </button>

                        <!-- User Avatar (Mobile) -->
                        <div class="lg:hidden w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                            <span class="text-xs font-semibold text-primary-foreground">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-3 sm:p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
