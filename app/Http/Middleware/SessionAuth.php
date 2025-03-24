<?php

namespace App\Http\Middleware;

use App\Enums\UserError;
use App\Factories\ResponseFactory;
use App\Models\Hr\User;

class SessionAuth
{
    public function handle($request, \Closure $next)
    {
        $user = User::auth();

        if ($user instanceof UserError) {
            return ResponseFactory::error($user->value, ['code' => 'invalid_token'], null, 401);
        }

        return $next($request);
    }
}
