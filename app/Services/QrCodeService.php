<?php

namespace App\Services;

use App\Models\QrAttendanceToken;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Str;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Token expiry time in seconds (default: 60 seconds).
     */
    protected int $tokenExpiry = 60;

    /**
     * Generate a new QR code token for a location.
     */
    public function generateToken(int $locationId, int $adminId): QrAttendanceToken
    {
        // Clean up OLD expired tokens (not current ones!)
        // Only delete tokens expired more than 1 hour ago
        $this->cleanupExpiredTokens($locationId);

        $token = QrAttendanceToken::create([
            'token' => $this->generateSecureToken(),
            'location_id' => $locationId,
            'created_by' => $adminId,
            'expires_at' => Carbon::now()->addSeconds($this->tokenExpiry),
        ]);

        \Log::info('Created new token', [
            'token_id' => $token->id,
            'location_id' => $locationId,
            'expires_at' => $token->expires_at
        ]);

        return $token;
    }

    /**
     * Get current valid token for a location or generate new one.
     */
    public function getCurrentToken(int $locationId, int $adminId): QrAttendanceToken
    {
        // Try to find an existing valid token
        // Note: We don't check used_at because QR codes should be reusable by multiple students
        $token = QrAttendanceToken::where('location_id', $locationId)
            ->where('expires_at', '>', Carbon::now())
            ->orderBy('expires_at', 'desc')
            ->first();

        if ($token) {
            \Log::info('Reusing existing token', [
                'token_id' => $token->id,
                'expires_at' => $token->expires_at,
                'expires_in' => $token->expires_at->diffInSeconds(Carbon::now())
            ]);
            return $token;
        }

        // Generate new token if none exists
        \Log::info('Generating new token for location', ['location_id' => $locationId]);
        return $this->generateToken($locationId, $adminId);
    }

    /**
     * Generate QR code image (SVG).
     */
    public function generateQrCodeImage(QrAttendanceToken $token): string
    {
        $data = json_encode([
            'token' => $token->token,
            'location_id' => $token->location_id,
            'expires_at' => $token->expires_at->toIso8601String(),
        ]);

        $renderer = new ImageRenderer(
            new RendererStyle(300, 2),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        return $writer->writeString($data);
    }

    /**
     * Validate a QR token.
     */
    public function validateToken(string $tokenString): array
    {
        $token = QrAttendanceToken::where('token', $tokenString)->first();

        if (!$token) {
            return [
                'valid' => false,
                'message' => 'Invalid QR code',
                'token' => null,
            ];
        }

        if ($token->isExpired()) {
            return [
                'valid' => false,
                'message' => 'QR code has expired',
                'token' => $token,
            ];
        }

        if ($token->isUsed()) {
            return [
                'valid' => false,
                'message' => 'QR code has already been used',
                'token' => $token,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Valid QR code',
            'token' => $token,
        ];
    }

    /**
     * Generate a secure random token.
     */
    protected function generateSecureToken(): string
    {
        return hash('sha256', Str::random(64) . microtime(true));
    }

    /**
     * Clean up expired tokens for a location.
     * Only deletes tokens that expired more than 1 hour ago.
     */
    protected function cleanupExpiredTokens(int $locationId): void
    {
        $deleted = QrAttendanceToken::where('location_id', $locationId)
            ->where('expires_at', '<', Carbon::now()->subHour())
            ->delete();
            
        if ($deleted > 0) {
            \Log::info('Cleaned up old tokens', [
                'location_id' => $locationId,
                'deleted_count' => $deleted
            ]);
        }
    }

    /**
     * Revoke a token manually.
     */
    public function revokeToken(int $tokenId): bool
    {
        $token = QrAttendanceToken::find($tokenId);
        
        if (!$token) {
            return false;
        }

        $token->update(['expires_at' => Carbon::now()]);
        return true;
    }
}
