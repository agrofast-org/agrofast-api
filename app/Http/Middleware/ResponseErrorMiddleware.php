<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Support\Traits\HandlesJsonErrors;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ResponseErrorMiddleware
{
    use HandlesJsonErrors;

    /**
     * Handle an incoming request.
     *
     * @param mixed $request
     */
    public function handle($request, \Closure $next)
    {
        try {
            $response = $next($request);

            if (isset($response->exception)) {
                throw $response->exception;
            }

            return $response;
        } catch (InvalidFormException|ValidationException $e) {
            return $this->returnValidationErrors($e);
        } catch (InvalidRequestException $e) {
            return response()->json(
                $e->data(),
                $this->validHttpCode($e->getCode(), Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], $this->validHttpCode($e->getCode(), Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
