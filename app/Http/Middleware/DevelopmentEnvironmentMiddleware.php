<?php

namespace App\Http\Middleware;

use Closure;

class DevelopmentEnvironmentMiddleware
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
        if (env('APP_ENV') !== 'local') {
            return response()->json(['message' => 'Not allowed environment'], 404);
        } else {
            return $next($request);
        }
    }
}
