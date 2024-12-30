<?php

namespace App\Http\Middleware;

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
            // return response()->json(['message' => 'An error occurred', 'error' => $e], 500);

            $response = $next($request);
            DB::commit();

            return $response;
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json(['message' => 'An error occurred', 'error' => $e], 500);
        }
    }
}
