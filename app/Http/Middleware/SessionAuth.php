<?php

namespace App\Http\Middleware;

use App\Enums\UserError;
use App\Models\Hr\User;

class SessionAuth
{
    public function handle($request, \Closure $next)
    {
        $session = User::session();

        if ($session instanceof UserError) {
            return response()->json(['message' => $session->value], 401);
        }

        return $next($request);
    }
}
