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
            $response = $next($request);
            DB::commit();

            return $response;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
