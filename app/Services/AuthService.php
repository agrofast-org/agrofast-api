<?php

namespace App\Services;

use App\Enums\UserAction;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
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


        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();
        $remember = RememberBrowser::where([
            'user_id' => $user->id,
            'browser_agent_id' => $browserAgent->id,
            ['created_at', '>=', Carbon::now()->subDays(30)],
        ])->first();

        $authCode = $remember ? null : AuthCode::createCode($user->id, AuthCode::EMAIL);
        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        if ($remember) {
            $session->update(['authenticated' => true]);
        }

        if (! empty($credentials['remember']) && $credentials['remember'] === 'true' && ! $remember) {
            RememberBrowser::create([
                'user_id'          => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            'user'    => $user,
            'token'   => $jwt,
            'session' => $session,
            'auth' => $remember ? UserAction::AUTHENTICATED->value : UserAction::AUTHENTICATE->value,
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

        $session = User::session();
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
        if ($authCode->auth_type === AuthCode::SMS) {
            $user->update(['number_authenticated' => true]);
        } else {
            $user->update(['email_authenticated' => true]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            'user'  => $user,
            'token' => $jwt,
        ];
    }
}
