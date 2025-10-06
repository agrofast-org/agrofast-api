<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResponseErrorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        try {
            $response = $next($request);

            if (isset($response->exception)) {
                throw $response->exception;
            }

            return $response;
        } catch (InvalidFormException $e) {
            return $this->returnErrors($e);
        } catch (ValidationException $e) {
            return $this->returnErrors($e);
        } catch (InvalidRequestException $e) {
            return response()->json($e->data(), $e->getCode() ?: 400);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], $response->status() ?: 500);
        }
    }

    protected function returnErrors(InvalidFormException|ValidationException $e)
    {
        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors(),
        ], $e->status ?? 422);
    }
}
