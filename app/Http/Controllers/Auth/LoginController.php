<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                
                try {
                    // Issue JWT token via Laravel Sanctum
                    $token = $user->createToken('auth-token', ['*'], now()->addHours(8))->plainTextToken;
                } catch (\Exception $e) {
                    \Log::error('Token creation failed: ' . $e->getMessage());
                    // Continue without token for web requests
                    $token = null;
                }
                
                // For API requests, return JSON with token
                if ($request->expectsJson()) {
                    if (!$token) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Login successful but token creation failed',
                        ], 500);
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Login successful',
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                        ],
                    ], 200);
                }
                
                // For web requests, regenerate session and redirect
                $request->session()->regenerate();
                
                // Redirect based on role
                if ($user->isAdmin()) {
                    return redirect()->intended('/admin/dashboard');
                } elseif ($user->isSupervisor()) {
                    return redirect()->intended('/supervisor/dashboard');
                }
                
                return redirect()->intended('/student/dashboard');
            }

            // Authentication failed - log the failed attempt
            try {
                $this->activityLogService->logFailedLogin($request, $credentials['email']);
            } catch (\Exception $e) {
                \Log::error('Failed to log failed login: ' . $e->getMessage());
                // Continue even if logging fails
            }

            // Authentication failed
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        } catch (ValidationException $e) {
            throw $e; // Re-throw validation exceptions
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A database error occurred. Please try again.',
                ], 500);
            }
            
            return back()->withErrors([
                'email' => 'A database error occurred. Please try again or contact support if the problem persists.',
            ])->withInput($request->only('email'));
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // For API requests with token authentication
        if ($request->user() && $request->expectsJson()) {
            // Revoke current token
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ], 200);
        }
        
        // For web requests
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
}
