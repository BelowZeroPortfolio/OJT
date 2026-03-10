<?php

namespace App\Services;

use App\Models\Location;

class GeofencingService
{
    /**
     * Verify if coordinates are within location's geofence.
     */
    public function verifyGeofence(Location $location, ?float $latitude, ?float $longitude): array
    {
        // If geofencing is not enforced, allow
        if (!$location->enforce_geofence) {
            return [
                'verified' => true,
                'message' => 'Geofencing not enforced for this location',
                'distance' => null,
            ];
        }

        // If location doesn't have coordinates set, allow
        if (!$location->latitude || !$location->longitude) {
            return [
                'verified' => true,
                'message' => 'Location coordinates not configured',
                'distance' => null,
            ];
        }

        // If user didn't provide coordinates, reject
        if ($latitude === null || $longitude === null) {
            return [
                'verified' => false,
                'message' => 'Location permission required for attendance',
                'distance' => null,
            ];
        }

        // Calculate distance
        $distance = $location->calculateDistance($latitude, $longitude);
        $isWithin = $distance <= $location->geofence_radius;

        return [
            'verified' => $isWithin,
            'message' => $isWithin 
                ? 'Within geofence' 
                : sprintf('Outside geofence (%.0fm away, max: %dm)', $distance, $location->geofence_radius),
            'distance' => round($distance, 2),
        ];
    }

    /**
     * Get user-friendly distance message.
     */
    public function getDistanceMessage(float $distance): string
    {
        if ($distance < 1000) {
            return sprintf('%.0f meters away', $distance);
        }

        return sprintf('%.1f kilometers away', $distance / 1000);
    }
}
