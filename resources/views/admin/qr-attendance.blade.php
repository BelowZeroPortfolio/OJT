@extends('layouts.admin')

@section('page-title', 'QR Code Attendance')
@section('page-description', 'Display QR codes for student scanning')

@section('content')
<!-- Info Card -->
<div class="bg-card rounded-lg shadow-sm border border-border p-6 mb-6">
    <div class="flex items-start gap-4">
        <div class="p-3 bg-primary/10 rounded-lg">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-semibold text-foreground mb-2">How QR Code Attendance Works</h3>
            <ul class="text-sm text-muted-foreground space-y-1">
                <li>• Click on a location card to open its QR code display in a new tab</li>
                <li>• QR codes automatically refresh every 60 seconds for security</li>
                <li>• Students scan the code with their mobile devices to record attendance</li>
            </ul>
        </div>
    </div>
</div>

<!-- Locations Grid -->
@if($locations->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($locations as $location)
            <a href="{{ route('admin.qr-display', $location->id) }}" 
               target="_blank"
               class="group bg-card rounded-lg shadow-sm border border-border hover:shadow-md hover:border-primary/50 transition-all duration-200">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-primary/10 rounded-lg group-hover:bg-primary/20 transition-colors">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-foreground group-hover:text-primary transition-colors">
                                    {{ $location->name }}
                                </h3>
                                <p class="text-xs text-muted-foreground">ID: {{ $location->id }}</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        @if($location->address)
                            <div class="flex items-start gap-2 text-sm text-muted-foreground">
                                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                </svg>
                                <span>{{ $location->address }}</span>
                            </div>
                        @endif
                        
                        @if(($location->users_count ?? 0) > 0)
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span>{{ $location->users_count }} students</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="pt-4 border-t border-border">
                        <div class="flex items-center justify-center gap-2 text-sm font-medium text-primary group-hover:text-primary/80 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                            Display QR Code
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@else
    <!-- Empty State -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-12 text-center">
        <div class="max-w-md mx-auto">
            <div class="bg-muted/50 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-foreground mb-2">No Locations Available</h3>
            <p class="text-muted-foreground mb-6">Create locations first to generate QR codes for attendance tracking.</p>
            <a href="{{ route('admin.locations.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg font-medium hover:bg-primary/90 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Location
            </a>
        </div>
    </div>
@endif
@endsection
