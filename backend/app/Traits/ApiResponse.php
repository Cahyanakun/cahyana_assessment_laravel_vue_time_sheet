<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response.
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $code);
    }

    /**
     * Error response.
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation error response.
     */
    protected function validationError($errors, string $message = 'Validation Error'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }
}
