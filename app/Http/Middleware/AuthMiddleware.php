<?php

namespace App\Http\Middleware;

use App\Models\Hr\Session;
use App\Models\Hr\User;
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

        $session = Session::where([
            'id' => $decodedToken->sid,
        ])->first();

        if ($session->authenticated === false) {
            return response()->json(['message' => 'unauthenticated'], 401);
        }

        return $next($request);
    }
}
