<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'date',
        'time_in',
        'time_out',
        'total_hours',
        'scan_type',
        'scanner_ip',
        'is_valid',
        'validation_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'total_hours' => 'decimal:2',
        'is_valid' => 'boolean',
    ];

    /**
     * Get the user that owns the attendance record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location for this attendance record.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Calculate and return total hours.
     * This accessor automatically calculates total_hours if time_out is set.
     */
    public function getTotalHoursAttribute($value)
    {
        // If total_hours is already set, return it
        if ($value !== null) {
            return $value;
        }

        // If time_out is set, calculate the difference
        if ($this->time_out && $this->time_in) {
            $timeIn = Carbon::parse($this->time_in);
            $timeOut = Carbon::parse($this->time_out);
            return round($timeOut->diffInMinutes($timeIn) / 60, 2);
        }

        return null;
    }
}
