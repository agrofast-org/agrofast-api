<?php

namespace App\Services;

use App\Enums\UserAction;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Error;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\User;
use App\Models\Success;
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
     *
     * @return Error|Success Result containing user, token, session, or error
     */
    public function login(array $credentials, Request $request): Error|Success
    {
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            return new Error('user_not_found', ['email' => 'user_not_found']);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return new Error('wrong_password', ['password' => 'wrong_password']);
        }

        $browserFingerprint = $request->header('Browser-Agent');
        $browserAgent = BrowserAgent::where('fingerprint', $browserFingerprint)->first();
        if (!$browserAgent) {
            return new Error('browser_agent_not_found');
        }

        $remember = RememberBrowser::where('user_id', $user->id)
            ->where('browser_agent_id', $browserAgent->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->first()
        ;

        $authType = AuthCode::EMAIL;
        $authCode = ($user->email_verified && $remember) ? null : AuthCode::createCode($user->id, $authType);

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        if ($user->email_verified && $remember) {
            $session->update(['authenticated' => true]);
        }

        if (!empty($credentials['remember']) && $credentials['remember'] === 'true' && !$remember) {
            RememberBrowser::create([
                'user_id' => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = TokenFactory::create($user, $session);

        return new Success('login_success', [
            'user' => UserDataResponse::format($user),
            'token' => $jwt,
            'auth' => ($user->email_verified && $remember) ? UserAction::AUTHENTICATED->value : UserAction::AUTHENTICATE->value,
        ]);
    }

    /**
     * @return Error|Success the result of the resend operation
     */
    public function resendCode(): Error|Success
    {
        // TODO: Implement resendCode() method.
        return new Error('not_implemented');
    }

    /**
     * Performs user authentication using the verification code.
     *
     * @param Request $request Request instance
     *
     * @return Error|Success Result containing user and new token, or error
     */
    public function authenticate(Request $request): Error|Success
    {
        $user = User::auth();
        if (!$user) {
            return new Error('user_not_authenticated');
        }

        $session = User::session();
        if (!$session) {
            return new Error('session_not_found');
        }

        $authCode = AuthCode::where('id', $session->auth_code_id)
            ->where('auth_type', AuthCode::EMAIL)
            ->first()
        ;

        if (!$authCode) {
            return new Error('invalid_authentication_code');
        }

        $codeInput = $request->input('code');
        if ($authCode->code !== $codeInput) {
            $updatedAttempts = $authCode->attempts + 1;
            if ($updatedAttempts >= AuthCode::MAX_ATTEMPTS) {
                $authCode->update(['attempts' => $updatedAttempts, 'active' => false]);
                $session->update(['authenticated' => false, 'active' => false]);

                return new Error('authentication_code_attempts_exceeded', null, null, 401);
            }
            $authCode->update(['attempts' => $updatedAttempts]);

            return new Error('incorrect_authentication_code', [
                'code' => 'incorrect_authentication_code',
            ], [
                'attempts' => AuthCode::MAX_ATTEMPTS - $updatedAttempts,
            ], 400);
        }

        $authCode->update(['active' => false]);
        $session->update(['authenticated' => true]);

        if ($authCode->auth_type === AuthCode::EMAIL) {
            $user->update(['email_verified' => true, 'email_verified_at' => Carbon::now()]);
        } elseif ($authCode->auth_type === AuthCode::SMS) {
            $user->update(['number_verified' => true, 'number_verified_at' => Carbon::now()]);
        }

        $jwt = TokenFactory::create($user, $session);

        return new Success('authentication_success', [
            'user' => UserDataResponse::format($user),
            'token' => $jwt,
        ]);
    }
}
