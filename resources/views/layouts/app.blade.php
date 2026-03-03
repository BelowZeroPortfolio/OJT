<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'OJT Attendance System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen" x-data="{ mobileMenuOpen: false }">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Desktop Navigation -->
                    <div class="flex items-center space-x-8">
                        <div class="flex items-center">
                            <h1 class="text-xl font-semibold text-gray-900">OJT Attendance</h1>
                        </div>
                        
                        @auth
                            @if(Auth::user()->isAdmin())
                            <!-- Admin Desktop Menu -->
                            <div class="hidden md:flex space-x-1">
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.users.index') }}" 
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    Students
                                </a>
                                <a href="{{ route('admin.locations.index') }}" 
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.locations.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    Locations
                                </a>
                            </div>
                            @elseif(Auth::user()->isStudent())
                            <!-- Student Desktop Menu -->
                            <div class="hidden md:flex space-x-1">
                                <a href="{{ route('student.dashboard') }}" 
                                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('student.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                    My Attendance
                                </a>
                            </div>
                            @endif
                        @endauth
                    </div>

                    <!-- Desktop User Menu -->
                    <div class="hidden md:flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-3">
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</p>
                                </div>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                Login
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="flex items-center md:hidden">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                                type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-500 transition-colors"
                                aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Hamburger icon -->
                            <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Close icon -->
                            <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="md:hidden border-t border-gray-200"
                 style="display: none;">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    @auth
                        @if(Auth::user()->isAdmin())
                        <!-- Admin Mobile Menu -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users.index') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            Students
                        </a>
                        <a href="{{ route('admin.locations.index') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.locations.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            Locations
                        </a>
                        @elseif(Auth::user()->isStudent())
                        <!-- Student Mobile Menu -->
                        <a href="{{ route('student.dashboard') }}" 
                           class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('student.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            My Attendance
                        </a>
                        @endif
                    @endauth
                </div>
                
                <!-- Mobile User Info -->
                <div class="pt-4 pb-3 border-t border-gray-200">
                    @auth
                        <div class="px-4 mb-3">
                            <p class="text-base font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ ucfirst(Auth::user()->role) }}</p>
                        </div>
                        <div class="px-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="px-2">
                            <a href="{{ route('login') }}" 
                               class="block w-full text-center px-3 py-2 rounded-md text-base font-medium text-white bg-gray-900 hover:bg-gray-800">
                                Login
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
