<?php

namespace App\Services;

use App\Http\Requests\User\UserStoreRequest;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Creates a new user and starts a session.
     *
     * @param array   $data    user data to be inserted
     * @param UserStoreRequest $request request instance
     *
     * @return array result with user and token or errors
     */
    public function createUser(array $data, UserStoreRequest $request): array
    {
        if (! empty($validated)) {
            return ['error' => $validated];
        }

        $existingUser = User::where('number', $data['number'])->first();
        if ($existingUser) {
            return [
                'error' => [
                    'number' => 'user_already_exists',
                ],
            ];
        }

        $user = User::create($data);

        $authCode = AuthCode::createCode($user->id, AuthCode::SMS);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();

        $sessionData = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser_agent_id' => $browserAgent->id,
            'auth_code_id' => $authCode->id,
            'auth_type' => 'email',
            'last_activity' => Carbon::now()->timestamp,
        ];
        $session = Session::create($sessionData);

        if (! empty($data['remember']) && $data['remember'] === 'true') {
            RememberBrowser::create([
                'user_id' => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'sid' => $session->id,
                'aud' => 'agrofast-app-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
            ],
            env('APP_KEY'),
            'HS256'
        );

        return [
            'user' => $user,
            'token' => $jwt,
            'session' => $session,
        ];
    }
}
