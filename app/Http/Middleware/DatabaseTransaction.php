<?php

namespace App\Http\Middleware;

use App\Models\ErrorLog;
use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseTransaction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        DB::beginTransaction();
        try {
            $response = $next($request);

            DB::commit();

            return $response;
        } catch (Throwable $e) {
            DB::rollBack();

            ErrorLog::create([
                'url' => $request->url(),
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            logger()->error($e);

            return response()->json([
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }
}
