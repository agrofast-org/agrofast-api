<?php

namespace App\Http\Middleware;

use App\Models\Hr\User;
use Closure;

class AuthBasicMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = User::auth();

        if (typeof($user) === 'enum') {
            return response()->json(['message' => $user->value], 401);
        }

        return $next($request);
    }
}
