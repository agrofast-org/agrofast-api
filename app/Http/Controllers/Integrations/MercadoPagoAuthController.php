<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Hr\User;
use App\Models\Hr\UserMercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MercadoPagoAuthController extends Controller
{
    /**
     * Gera o link de conexão OAuth para o transportador.
     */
    public function connect()
    {
        $clientId = config('services.mercadopago.client_id');
        $redirectUri = config('services.mercadopago.redirect_uri');

        $authUrl = 'https://auth.mercadopago.com.br/authorization'.
            "?client_id={$clientId}".
            '&response_type=code'.
            '&platform_id=mp'.
            '&redirect_uri='.urlencode($redirectUri);

        return response()->json([
            'authorization_url' => $authUrl,
        ]);
    }

    /**
     * Callback OAuth recebido do Mercado Pago.
     * Troca o código de autorização por access_token e salva os dados.
     */
    public function callback(Request $request)
    {
        $code = $request->query('code');
        $user = User::auth();

        if (!$code || !$user) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', [
            'client_secret' => config('services.mercadopago.client_secret'),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('services.mercadopago.redirect_uri'),
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to obtain access token',
                'details' => $response->json(),
            ], 400);
        }

        $data = $response->json();

        $account = UserMercadoPago::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $user->name,
                'cpf' => $user->cpf,
                'email' => $user->email,
                'mp_user_id' => $data['user_id'] ?? null,
                'mp_access_token' => $data['access_token'] ?? null,
                'mp_refresh_token' => $data['refresh_token'] ?? null,
                'mp_token_expires_at' => now()->addSeconds($data['expires_in'] ?? 21600),
                'status' => 'connected',
            ]
        );

        return response()->json([
            'message' => 'Conta Mercado Pago conectada com sucesso.',
            'account' => $account,
        ]);
    }
}
