<?php

namespace App\Services;

use App\Models\Hr\AuthEmail;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;

class UserService
{
    /**
     * Creates a new user and starts a session.
     *
     * @param array   $data    user data to be inserted
     * @param Request $request request instance
     *
     * @return array result with user and token or errors
     */
    public function createUser(array $data, Request $request): array
    {
        $params = User::prepareInsert($data);
        $validated = User::validateInsert(User::prepareInsert($params));

        if (! empty($validated)) {
            return ['error' => $validated];
        }

        $existingUser = User::where('email', $params['email'])->first();
        if ($existingUser) {
            return [
                'error' => [
                    'email' => 'user_already_exists',
                ],
            ];
        }

        $user = User::create($params);

        $authCode = AuthEmail::createCode($user->id);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();

        $sessionData = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser_agent_id' => $browserAgent->id,
            'auth_code_id' => $authCode->id,
            'auth_type' => 'auth_email',
            'last_activity' => Carbon::now()->timestamp,
        ];
        $session = Session::create($sessionData);

        if (! empty($params['remember']) && $params['remember'] === 'true') {
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
                'aud' => 'mdxfy-app-services',
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
