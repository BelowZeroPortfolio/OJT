@extends('layouts.admin')

@section('page-title', 'Activity Logs')
@section('page-description', 'View system activity and user actions')

@section('content')
<div>
    <!-- Filters -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-4 mb-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">User</label>
                <select class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring text-foreground">
                    <option>All Users</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Action Type</label>
                <select class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring text-foreground">
                    <option>All Actions</option>
                    <option>Login</option>
                    <option>Logout</option>
                    <option>Check In</option>
                    <option>Check Out</option>
                    <option>Create</option>
                    <option>Update</option>
                    <option>Delete</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Date From</label>
                <input type="date" class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring text-foreground">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted-foreground mb-1.5">Date To</label>
                <input type="date" class="w-full px-3 py-2 text-sm bg-background border border-input rounded-lg focus:ring-2 focus:ring-ring text-foreground">
            </div>
        </form>
    </div>

    <!-- Activity Timeline -->
    <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        <div class="px-6 py-4 border-b border-border">
            <h3 class="text-lg font-semibold text-foreground">Recent Activity</h3>
        </div>
        
        <!-- Desktop View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Details</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-card divide-y divide-border">
                    <!-- Sample Data -->
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ now()->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted-foreground">Admin</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-foreground border border-border">
                                Login
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-muted-foreground">
                            User logged into the system
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            127.0.0.1
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ now()->subMinutes(15)->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">Sample Student</div>
                            <div class="text-xs text-muted-foreground">Student</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-secondary-foreground border border-border">
                                Check In
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-muted-foreground">
                            Checked in at Main Office
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            192.168.1.100
                        </td>
                    </tr>
                    <tr class="hover:bg-muted/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">
                            {{ now()->subHours(1)->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-foreground">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-muted-foreground">Admin</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground border border-border">
                                Update
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-muted-foreground">
                            Updated student information
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                            127.0.0.1
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden divide-y divide-border">
            <div class="p-4 space-y-3 hover:bg-muted/50 transition-colors">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm font-medium text-foreground">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-muted-foreground">Admin</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-foreground border border-border">
                        Login
                    </span>
                </div>
                <p class="text-sm text-muted-foreground">User logged into the system</p>
                <div class="flex justify-between text-xs text-muted-foreground">
                    <span>{{ now()->format('M d, Y h:i A') }}</span>
                    <span>127.0.0.1</span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-border">
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing 1 to 3 of 3 results
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
