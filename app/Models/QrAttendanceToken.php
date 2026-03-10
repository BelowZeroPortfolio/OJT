<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class QrAttendanceToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'location_id',
        'created_by',
        'expires_at',
        'used_at',
        'used_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the location for this token.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the admin who created this token.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who used this token.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if token has been used.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Check if token is valid (not expired and not used).
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isUsed();
    }

    /**
     * Mark token as used.
     */
    public function markAsUsed(int $userId): void
    {
        $this->update([
            'used_at' => Carbon::now(),
            'used_by' => $userId,
        ]);
    }
}
