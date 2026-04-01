<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_code',
        'name',
        'address',
        'latitude',
        'longitude',
        'geofence_radius',
        'enforce_geofence',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'geofence_radius' => 'integer',
        'enforce_geofence' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the attendance records for this location.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get the users assigned to this location.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'assigned_location_id');
    }

    /**
     * Get the supervisor for this location.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get students assigned to this location.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'assigned_location_id')
                    ->where('role', 'student');
    }

    /**
     * Get QR tokens for this location.
     */
    public function qrTokens()
    {
        return $this->hasMany(QrAttendanceToken::class);
    }

    /**
     * Scope a query to only include active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', '=', true);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     * Returns distance in meters.
     */
    public function calculateDistance(float $lat, float $lon): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within geofence.
     */
    public function isWithinGeofence(float $lat, float $lon): bool
    {
        if (!$this->enforce_geofence || !$this->latitude || !$this->longitude) {
            return true; // Geofencing disabled or not configured
        }

        $distance = $this->calculateDistance($lat, $lon);
        return $distance <= $this->geofence_radius;
    }
}
