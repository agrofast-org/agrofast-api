<?php

namespace App\Services;

use App\Enums\UserAction;
use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\User;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
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
     * @return array Result containing user, token, session, or error
     */
    public function login(array $credentials, UserLoginRequest $request): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!Hash::check($credentials['password'], $user->password)) {
            throw new InvalidFormException(__('validation.current_password'), ['password' => __('validation.current_password')]);
        }

        $browserFingerprint = $request->header('Browser-Agent');
        $browserAgent = BrowserAgent::where('fingerprint', $browserFingerprint)->first();

        $remember = RememberBrowser::where('user_id', $user->id)
            ->where('browser_agent_id', $browserAgent->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->first()
        ;

        $authType = AuthCode::EMAIL;
        $authCode = ($user->email_verified && ($user->email_two_factor_auth || $remember)) ? null : AuthCode::createCode($user->id, $authType);

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

        return [
            'user' => UserDataResponse::withDocument($user),
            'token' => $jwt,
            'auth' => ($user->email_verified && ($user->email_two_factor_auth || $remember)) ? UserAction::AUTHENTICATED->value : UserAction::AUTHENTICATE->value,
        ];
    }

    /**
     * @return array the result of the resend operation
     *
     * @throws ValidationException if the user is not authenticated or the session is not found
     */
    public function resendCode()
    {
        $user = User::auth();

        $session = User::session();

        $authCode = AuthCode::where('id', $session->auth_code_id)
            ->where('auth_type', AuthCode::EMAIL)
            ->first()
        ;
        if (!$authCode) {
            throw new ValidationException('invalid_authentication_code');
        }

        $timeoutSeconds = 60;
        if (Carbon::now()->diffInSeconds($authCode->created_at) < $timeoutSeconds) {
            throw new ValidationException('resend_timeout');
        }

        $attempts = $authCode->attempts;

        $authCode->update(['active' => false]);

        $newAuthCode = AuthCode::createCode($user->id, AuthCode::EMAIL);
        $newAuthCode->update(['attempts' => $attempts]);

        $session->update(['auth_code_id' => $newAuthCode->id]);

        return [
            'user' => UserDataResponse::withDocument($user),
        ];
    }

    /**
     * Performs user authentication using the verification code.
     *
     * @param Request $request Request instance
     *
     * @return array Result containing user and new token, or error
     */
    public function authenticate(Request $request)
    {
        $user = User::auth();

        $session = User::session();

        $authCode = AuthCode::where('id', $session->auth_code_id)
            ->where('auth_type', AuthCode::EMAIL)
            ->first()
        ;

        if (!$authCode) {
            throw new ValidationException('invalid_authentication_code');
        }

        $codeInput = $request->input('code');
        if ($authCode->code !== $codeInput) {
            $updatedAttempts = $authCode->attempts + 1;
            if ($updatedAttempts >= AuthCode::MAX_ATTEMPTS) {
                $authCode->update(['attempts' => $updatedAttempts, 'active' => false]);
                $session->update(['authenticated' => false, 'active' => false]);

                throw new ValidationException('authentication_code_attempts_exceeded', 401);
            }
            $authCode->update(['attempts' => $updatedAttempts]);

            throw new InvalidRequestException('incorrect_authentication_code', [
                'code' => 'incorrect_authentication_code',
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

        return [
            'user' => UserDataResponse::withDocument($user),
            'token' => $jwt,
        ];
    }
}
