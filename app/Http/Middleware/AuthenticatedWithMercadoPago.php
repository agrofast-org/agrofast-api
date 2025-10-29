<?php

namespace App\Http\Middleware;

use App\Exception\InvalidRequestException;
use App\Models\Hr\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedWithMercadoPago
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $user = User::auth()->load('user_mercado_pago');
        if (
            !$user
            || !$user->user_mercado_pago
            || !$user->user_mercado_pago->isConnected()
        ) {
            throw new InvalidRequestException('User not authenticated with Mercado Pago.', [], 401);
        }

        return $next($request);
    }
}
