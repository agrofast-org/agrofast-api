<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class DeveloperAuthMiddleware
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
        return $next($request);
    }
}
