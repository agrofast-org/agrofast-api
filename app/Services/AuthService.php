<?php

namespace App\Services;

use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Logs in the user.
     *
     * @param array   $credentials Authentication data (email, password, remember)
     * @param Request $request     Request instance
     * @return array Result with user, token, and session or error
     */
    public function login(array $credentials, Request $request): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return ['error' => ['email' => 'user_not_found']];
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return ['error' => ['password' => 'wrong_password']];
        }

        $authCode = AuthCode::createCode($user->id, AuthCode::SMS);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();

        $sessionData = [
            'user_id'          => $user->id,
            'auth_type'        => 'auth_email',
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'browser_agent_id' => $browserAgent->id,
            'auth_code_id'     => $authCode->id,
            'last_activity'    => Carbon::now()->timestamp,
        ];

        $session = Session::create($sessionData);

        if (! empty($credentials['remember']) && $credentials['remember'] === 'true') {
            RememberBrowser::create([
                'user_id'          => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'sid' => $session->id,
                'aud' => 'mdxfy-app-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
            ],
            env('APP_KEY'),
            'HS256'
        );

        return [
            'user'    => $user,
            'token'   => $jwt,
            'session' => $session,
        ];
    }

    /**
     * Authenticates the user using a verification code.
     *
     * @param Request $request Request instance
     * @return array Result with user and new token or error
     */
    public function authenticate(Request $request): array
    {
        $user = User::auth();

        if (! $user) {
            return ['error' => 'user_not_authenticated'];
        }

        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
        $session = Session::where('id', $decoded->sid)->first();
        $authCode = AuthCode::where(['id' => $session->auth_code_id, 'auth_type' => AuthCode::SMS])->first();

        if (! $authCode) {
            return ['error' => ['code' => 'invalid_authentication_code']];
        }

        $codeInput = $request->input('code');
        if ($authCode->code !== $codeInput) {
            if ($authCode->attempts + 1 >= 3) {
                $authCode->update(['active' => false]);

                return ['error' => ['code' => 'authentication_code_attempts_exceeded']];
            }
            $authCode->update(['attempts' => $authCode->attempts + 1]);

            return ['error' => ['code' => 'invalid_authentication_code']];
        }

        $authCode->update(['active' => false]);
        $user->update(['number_authenticated' => true]);

        $jwt = JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'sid' => $session->id,
                'aud' => 'mdxfy-app-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
            ],
            env('APP_KEY'),
            'HS256'
        );

        return [
            'user'  => $user,
            'token' => $jwt,
        ];
    }
}
