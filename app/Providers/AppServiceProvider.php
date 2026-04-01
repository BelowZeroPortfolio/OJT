<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // Log ALL database queries to identify slow ones
        DB::listen(function ($query) {
            // Log all queries ONLY in local development
            if (config('app.debug') && config('app.env') === 'local') {
                Log::info('Query executed', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
            
            // Always log slow queries (> 500ms)
            if ($query->time > 500) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
        });

        // Log slow HTTP requests (> 1 second)
        if (!app()->runningInConsole()) {
            $start = microtime(true);
            
            app()->terminating(function () use ($start) {
                $duration = (microtime(true) - $start) * 1000;
                
                if ($duration > 1000) {
                    Log::warning('Slow request detected', [
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'duration' => round($duration, 2) . 'ms',
                    ]);
                }
            });
        }
    }
}
