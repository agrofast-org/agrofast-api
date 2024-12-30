<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'authentication_token_not_provided'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
            $session = Session::where('id', $decoded->sid)->first();
            if (! $session) {
                return response()->json(['message' => 'user_not_authenticated'], Response::HTTP_UNAUTHORIZED);
            }
            Auth::loginUsingId($session->user_id);
        } catch (\Exception $e) {
            return response()->json(['message' => 'authentication_token'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
