<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminQrCodeController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Generate or get current QR code for a location.
     */
    public function generate(Request $request, int $locationId): JsonResponse
    {
        $location = Location::findOrFail($locationId);
        $admin = $request->user();

        // Get or generate current token
        $token = $this->qrCodeService->getCurrentToken($locationId, $admin->id);

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token->token,
                'location_id' => $token->location_id,
                'location_name' => $location->name,
                'expires_at' => $token->expires_at->toIso8601String(),
                'expires_in_seconds' => $token->expires_at->diffInSeconds(now()),
            ],
        ]);
    }

    /**
     * Get QR code image with token info.
     */
    public function getQrImage(Request $request, int $locationId)
    {
        $location = Location::findOrFail($locationId);
        $admin = $request->user();

        $token = $this->qrCodeService->getCurrentToken($locationId, $admin->id);
        
        // Cache QR code generation (same token = same QR)
        $cacheKey = 'qr_code_' . $token->id;
        $qrCode = cache()->remember($cacheKey, $token->expires_at, function () use ($token) {
            return $this->qrCodeService->generateQrCodeImage($token);
        });

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('X-Token-Expires-At', $token->expires_at->toIso8601String())
            ->header('X-Token-Expires-In', $token->expires_at->diffInSeconds(now()))
            ->header('Cache-Control', 'public, max-age=' . $token->expires_at->diffInSeconds(now()));
    }

    /**
     * Revoke a QR token.
     */
    public function revoke(Request $request, int $tokenId): JsonResponse
    {
        $revoked = $this->qrCodeService->revokeToken($tokenId);

        if (!$revoked) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully',
        ]);
    }
}
