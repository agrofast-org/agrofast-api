<?php

namespace App\Http\Middleware;

use App\Models\System\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTransaction
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
        DB::beginTransaction();

        try {
            $response = $next($request);

            if (isset($response->exception)) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            return $response;
        } catch (\Throwable $e) {
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
