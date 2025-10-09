<?php

namespace App\Support\Traits;

use App\Exception\InvalidFormException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait HandlesJsonErrors
{
    /**
     * Format and return validation-related errors.
     */
    protected function returnValidationErrors(InvalidFormException|ValidationException $e)
    {
        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], $this->validHttpCode($e->status ?? $e->getCode(), Response::HTTP_UNPROCESSABLE_ENTITY));
    }

    /**
     * Ensures that the HTTP status code is valid.
     */
    protected function validHttpCode($code, $fallback = 500): int
    {
        return is_int($code) && $code >= 100 && $code < 600
            ? $code
            : $fallback;
    }
}
