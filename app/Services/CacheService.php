<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    const SESSION_TTL = 1800; // 30 minutes
    const DASHBOARD_STATS_TTL = 300; // 5 minutes
    const LOCATION_LOOKUP_TTL = 3600; // 1 hour

    /**
     * Cache key prefixes
     */
    const PREFIX_STUDENT_ATTENDANCE = 'student_attendance_';
    const PREFIX_ADMIN_ATTENDANCE = 'admin_attendance_';
    const PREFIX_DASHBOARD_STATS = 'dashboard_stats_';
    const PREFIX_LOCATION_LOOKUP = 'location_lookup_';
    const PREFIX_USER_SESSION = 'user_session_';

    /**
     * Cache user session data.
     *
     * @param int $userId
     * @param mixed $data
     * @return bool
     */
    public function cacheUserSession(int $userId, $data): bool
    {
        $key = self::PREFIX_USER_SESSION . $userId;
        return Cache::put($key, $data, self::SESSION_TTL);
    }

    /**
     * Get cached user session data.
     *
     * @param int $userId
     * @return mixed
     */
    public function getUserSession(int $userId)
    {
        $key = self::PREFIX_USER_SESSION . $userId;
        return Cache::get($key);
    }

    /**
     * Cache dashboard statistics.
     *
     * @param string $type (student or admin)
     * @param int|null $userId
     * @param mixed $data
     * @return bool
     */
    public function cacheDashboardStats(string $type, ?int $userId, $data): bool
    {
        $key = self::PREFIX_DASHBOARD_STATS . $type . ($userId ? "_{$userId}" : '');
        return Cache::put($key, $data, self::DASHBOARD_STATS_TTL);
    }

    /**
     * Get cached dashboard statistics.
     *
     * @param string $type (student or admin)
     * @param int|null $userId
     * @return mixed
     */
    public function getDashboardStats(string $type, ?int $userId)
    {
        $key = self::PREFIX_DASHBOARD_STATS . $type . ($userId ? "_{$userId}" : '');
        return Cache::get($key);
    }

    /**
     * Cache location lookup data.
     *
     * @param int $locationId
     * @param mixed $data
     * @return bool
     */
    public function cacheLocationLookup(int $locationId, $data): bool
    {
        $key = self::PREFIX_LOCATION_LOOKUP . $locationId;
        return Cache::put($key, $data, self::LOCATION_LOOKUP_TTL);
    }

    /**
     * Get cached location lookup data.
     *
     * @param int $locationId
     * @return mixed
     */
    public function getLocationLookup(int $locationId)
    {
        $key = self::PREFIX_LOCATION_LOOKUP . $locationId;
        return Cache::get($key);
    }

    /**
     * Cache all active locations.
     *
     * @param mixed $data
     * @return bool
     */
    public function cacheAllLocations($data): bool
    {
        $key = self::PREFIX_LOCATION_LOOKUP . 'all';
        return Cache::put($key, $data, self::LOCATION_LOOKUP_TTL);
    }

    /**
     * Get all cached active locations.
     *
     * @return mixed
     */
    public function getAllLocations()
    {
        $key = self::PREFIX_LOCATION_LOOKUP . 'all';
        return Cache::get($key);
    }

    /**
     * Invalidate student attendance cache.
     *
     * @param int $userId
     * @return bool
     */
    public function invalidateStudentAttendance(int $userId): bool
    {
        $key = self::PREFIX_STUDENT_ATTENDANCE . $userId;
        return Cache::forget($key);
    }

    /**
     * Invalidate admin attendance cache.
     * This clears all admin attendance caches since filters can vary.
     *
     * @return bool
     */
    public function invalidateAdminAttendance(): bool
    {
        // Clear all admin attendance caches by pattern
        return Cache::flush(); // In production, use tags or more specific pattern matching
    }

    /**
     * Invalidate dashboard statistics cache.
     *
     * @param string|null $type (student or admin, null for both)
     * @param int|null $userId
     * @return bool
     */
    public function invalidateDashboardStats(?string $type = null, ?int $userId = null): bool
    {
        if ($type && $userId) {
            $key = self::PREFIX_DASHBOARD_STATS . $type . "_{$userId}";
            return Cache::forget($key);
        } elseif ($type) {
            // Clear all stats for this type
            // In production, use cache tags for better granularity
            return Cache::flush();
        } else {
            // Clear all dashboard stats
            return Cache::flush();
        }
    }

    /**
     * Invalidate location cache.
     *
     * @param int|null $locationId (null to clear all locations)
     * @return bool
     */
    public function invalidateLocationCache(?int $locationId = null): bool
    {
        if ($locationId) {
            $key = self::PREFIX_LOCATION_LOOKUP . $locationId;
            Cache::forget($key);
        }
        
        // Always clear the "all locations" cache
        $allKey = self::PREFIX_LOCATION_LOOKUP . 'all';
        return Cache::forget($allKey);
    }

    /**
     * Invalidate user session cache.
     *
     * @param int $userId
     * @return bool
     */
    public function invalidateUserSession(int $userId): bool
    {
        $key = self::PREFIX_USER_SESSION . $userId;
        return Cache::forget($key);
    }

    /**
     * Invalidate all caches related to attendance record creation.
     * This should be called when a new attendance record is created.
     *
     * @param int $userId
     * @return void
     */
    public function invalidateOnAttendanceCreation(int $userId): void
    {
        // Invalidate student's attendance cache
        $this->invalidateStudentAttendance($userId);
        
        // Invalidate admin attendance caches (all filters)
        $this->invalidateAdminAttendance();
        
        // Invalidate dashboard statistics
        $this->invalidateDashboardStats('student', $userId);
        $this->invalidateDashboardStats('admin', null);
    }

    /**
     * Invalidate all caches related to user updates.
     * This should be called when a user is created, updated, or deleted.
     *
     * @param int $userId
     * @return void
     */
    public function invalidateOnUserUpdate(int $userId): void
    {
        // Invalidate user session
        $this->invalidateUserSession($userId);
        
        // Invalidate student's attendance cache
        $this->invalidateStudentAttendance($userId);
        
        // Invalidate admin attendance caches
        $this->invalidateAdminAttendance();
        
        // Invalidate dashboard statistics
        $this->invalidateDashboardStats();
    }

    /**
     * Invalidate all caches related to location updates.
     * This should be called when a location is created, updated, or deleted.
     *
     * @param int|null $locationId
     * @return void
     */
    public function invalidateOnLocationUpdate(?int $locationId = null): void
    {
        // Invalidate location cache
        $this->invalidateLocationCache($locationId);
        
        // Invalidate admin attendance caches (location filters may be affected)
        $this->invalidateAdminAttendance();
        
        // Invalidate dashboard statistics
        $this->invalidateDashboardStats();
    }
}
