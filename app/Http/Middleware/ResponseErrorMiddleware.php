<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Models\System\ErrorLog;
use Illuminate\Http\Request;

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
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->getErrors(),
            ], 422);
        } catch (\Throwable $e) {
            // ErrorLog::create([
            //     'url' => $request->url(),
            //     'error_message' => $e->getMessage(),
            //     'stack_trace' => $e->getTraceAsString(),
            //     'request_data' => $request->all(),
            // ]);
            // logger()->error($e);

            return response()->json([
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }
}
