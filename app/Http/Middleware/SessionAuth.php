<?php

namespace App\Http\Middleware;

use App\Enums\UserError;
use App\Factories\ResponseFactory;
use App\Models\Hr\User;

class SessionAuth
{
    public function handle($request, \Closure $next)
    {
        $session = User::session();

        if ($session instanceof UserError) {
            return ResponseFactory::error($session->value, ['code' => 'invalid_token'], null, 401);
        }

        return $next($request);
    }
}
