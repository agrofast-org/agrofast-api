<?php

namespace App\Http\Middleware;

use App\Enums\UserError;
use App\Factories\ResponseFactory;
use App\Models\Hr\Session;
use App\Models\Hr\User;

class UserAuth
{
    public function handle($request, \Closure $next)
    {
        $user = User::auth();

        if ($user instanceof UserError) {
            return ResponseFactory::error($user->value, ['code' => 'invalid_token'], null, 401);
        }

        $decodedToken = User::getDecodedToken();

        $session = Session::where([
            'id' => $decodedToken->sid,
        ])->first();

        if ($session->authenticated === false) {
            return ResponseFactory::error('unauthenticated', ['code' => 'invalid_token'], null, 401);
        }

        return $next($request);
    }
}
