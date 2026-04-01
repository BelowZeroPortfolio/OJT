<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorQrController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display QR code page for supervisor's location.
     */
    public function display()
    {
        $supervisor = Auth::user();
        $location = $supervisor->supervisedLocation;
        
        if (!$location) {
            return view('supervisor.no-location');
        }
        
        return view('supervisor.qr-display', compact('location'));
    }

    /**
     * Generate QR code for supervisor's location.
     */
    public function generate()
    {
        $supervisor = Auth::user();
        $location = $supervisor->supervisedLocation;
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'No location assigned'
            ], 404);
        }
        
        try {
            $result = $this->qrCodeService->generateQrCode($location->id);
            
            return response()->json([
                'success' => true,
                'qr_image' => $result['qr_image'],
                'expires_at' => $result['expires_at'],
                'token' => $result['token']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
