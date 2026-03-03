<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    /**
     * Return a validation error response.
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Return an authentication error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function authenticationError(string $message = 'Authentication required'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    /**
     * Return an authorization error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function authorizationError(string $message = 'Access denied'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }

    /**
     * Return a server error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function serverError(string $message = 'An error occurred while processing your request'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }

    /**
     * Return a rate limit error response.
     *
     * @param int $retryAfter
     * @return JsonResponse
     */
    public static function rateLimitError(int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Too many requests',
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', $retryAfter);
    }

    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a not found error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFoundError(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 404);
    }

    /**
     * Return a conflict error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function conflictError(string $message = 'Conflict occurred'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 409);
    }
}
