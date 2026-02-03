<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse(string $message, $data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'errors'  => null,
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], $code);
    }
}
