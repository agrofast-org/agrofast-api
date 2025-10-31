<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Hr\User;
use App\Models\Hr\UserMercadoPago;
use App\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoAuthController extends Controller
{
    /**
     * Gera o link de conexão OAuth para o transportador se autenticar no Mercado Pago.
     */
    public function connect()
    {
        try {
            $redirectUri = config('services.mercadopago.full_redirect_uri');

            $codeVerifier = rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
            $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

            $session = User::session();
            $session->storage_set([
                'mp_code_verifier' => $codeVerifier,
                'mp_code_challenge' => $codeChallenge,
            ]);

            $query = http_build_query([
                'client_id' => config('services.mercadopago.client_id'),
                'response_type' => 'code',
                'platform_id' => 'mp',
                'redirect_uri' => $redirectUri,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => 'S256',
            ]);

            $authorizationUrl = "https://auth.mercadopago.com/authorization?{$query}";

            return response()->json([
                'authorization_url' => $authorizationUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Mercado Pago connect failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Não foi possível gerar a URL de autenticação do Mercado Pago.',
            ], 500);
        }
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
        $webUrl = Utils::isProduction() ? env('WEB_URL') : 'https://agrofast.mesf.app';
        $redirectUri = $webUrl.config('services.mercadopago.redirect_uri');

        $payload = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        $session = User::session();
        $codeVerifier = $session->storage_get('mp_code_verifier');
        $payload['client_id'] = config('services.mercadopago.client_id');
        $payload['client_secret'] = config('services.mercadopago.client_secret');
        $payload['code_verifier'] = $codeVerifier;

        $response = Http::asForm()->post('https://api.mercadopago.com/oauth/token', $payload);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to obtain access token',
                'details' => $response->json(),
            ], 400);
        }

        $data = $response->json();

        $data['user_id'] = $user->id;

        $account = UserMercadoPago::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json([
            'message' => 'Conta Mercado Pago conectada com sucesso.',
        ]);
    }
}
