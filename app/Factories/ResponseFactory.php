<?php

namespace App\Factories;

use App\Models\Error;
use App\Models\Success;
use Illuminate\Http\JsonResponse;

class ResponseFactory
{
    /**
     * Creates a JSON response based on the type of result provided.
     *
     * This method checks whether the given result is an instance of Success or Error and
     * then calls the respective method to build a JSON response. If the result is of an unexpected type,
     * it returns a default error response.
     *
     * @param Error|Success $result the result object, which may be either a success or error encapsulation
     * @param null|int      $code   optional HTTP status code to use in the response
     *
     * @return JsonResponse the generated JSON response based on the provided result
     */
    public static function create(Error|Success $result, ?int $code = null): JsonResponse
    {
        if ($result instanceof Success) {
            return self::success($result->message, $result->data, $code ?? 200);
        }
        if ($result instanceof Error) {
            return self::error($result->message, $result->data, $result->errors, $code ?? $result->code ?? 400);
        }

        return response()->json(['success' => false, 'message' => 'Invalid response type'], 500);
    }

    /**
     * Returns a standardized successful response.
     *
     * @param string $message Optional success message
     * @param mixed  $payload Data to be returned
     * @param int    $code    HTTP status code (default 200)
     */
    public static function success(string $message, $payload = null, ?int $code = 200): JsonResponse
    {
        $response = ['success' => true];

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($payload)) {
            if ($payload instanceof Success) {
                $response['data'] = $payload->data;
            } else {
                $response['data'] = $payload;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Returns a standardized error response.
     *
     * @param string     $message Error message
     * @param int        $code    HTTP status code (default 400)
     * @param mixed      $errors  Additional error details
     * @param null|mixed $payload Additional data returned
     */
    public static function error(string $message, $payload = null, $errors = null, int $code = 400): JsonResponse
    {
        $response = ['success' => false];

        if (!empty($message)) {
            $response['message'] = $message;
        }
        if (!empty($payload)) {
            $response['data'] = $payload;
        }
        if (!empty($errors)) {
            if ($errors instanceof Error) {
                $response['errors'] = $errors->errors;
            } else {
                $response['errors'] = $errors;
            }
        }

        return response()->json($response, $code);
    }
}
