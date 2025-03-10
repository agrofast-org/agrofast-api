<?php

namespace App\Http\Middleware;

use App\Models\System\ErrorLog;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseTransaction
{
    public function handle($request, Closure $next)
    {
        try {
            return DB::transaction(function () use ($request, $next) {
                $response = $next($request);

                if (isset($response->exception)) {
                    throw $response->exception;
                }

                return $response;
            });
        } catch (Throwable $e) {
            ErrorLog::create([
                'url'          => $request->url(),
                'error_message' => $e->getMessage(),
                'stack_trace'  => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            logger()->error($e);

            return response()->json([
                'message' => 'An error occurred',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }
}
