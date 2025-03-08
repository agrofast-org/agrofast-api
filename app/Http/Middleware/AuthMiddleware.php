<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class AuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $user = User::auth();

        if (typeof($user) === 'enum') {
            return response()->json(['message' => $user->value], 401);
        }

        $decodedToken = User::getDecodedToken();

        return $next($request);
    }
}
