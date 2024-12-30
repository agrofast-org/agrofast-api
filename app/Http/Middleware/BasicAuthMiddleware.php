<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'authentication_token_not_provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
            Auth::loginUsingId($decoded->sub);
        } catch (\Exception $e) {
            return response()->json(['message' => 'invalid_authentication_token', 'error' => $e], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
