<?php

namespace App\Http\Middleware;

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
    if (!$token) {
      return response()->json(['message' => 'Authorization token not provided'], Response::HTTP_UNAUTHORIZED);
    }

    try {
      $decoded = JWT::decode($token, new Key(env('APP_JWT_SECRET'), 'HS256'));
      Auth::loginUsingId($decoded->id);
    } catch (\Exception $e) {
      return response()->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
    }

    return $next($request);
  }
}
