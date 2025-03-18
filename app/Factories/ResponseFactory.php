<?php

namespace App\Factories;

use Illuminate\Http\JsonResponse;

class ResponseFactory
{
    /**
     * Returns a standardized successful response.
     *
     * @param string $message Optional success message
     * @param mixed  $payload Data to be returned
     * @param int    $code    HTTP status code (default 200)
     */
    public static function success(string $message, $payload = null, int $code = 200): JsonResponse
    {
        $response = ['success' => true];

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($payload)) {
            $response['data'] = $payload;
        }

        return response()->json($response, $code);
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $message Error message
     * @param int    $code    HTTP status code (default 400)
     * @param mixed  $errors  Additional error details
     */
    public static function error(string $message, $errors = null, int $code = 400): JsonResponse
    {
        $response = ['success' => false];

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
