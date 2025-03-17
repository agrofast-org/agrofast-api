<?php

namespace App\Services;

use App\Enums\UserAction;
use App\Factories\SessionFactory;
use App\Factories\TokenFactory;
use App\Http\Requests\User\UserStoreRequest;
use App\Models\Hr\AuthCode;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\RememberBrowser;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
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

        $data['password'] = Hash::make($data['password']);
        $data['uuid'] = Str::uuid()->toString();
        $user = User::create($data);

        $authCode = AuthCode::createCode($user->id, AuthCode::EMAIL);
        $browserAgent = BrowserAgent::where('fingerprint', $request->header('Browser-Agent'))->first();

        $session = SessionFactory::create($user, $request, $browserAgent, $authCode);

        if (! empty($data['remember']) && $data['remember'] === 'true') {
            RememberBrowser::create([
                'user_id' => $user->id,
                'browser_agent_id' => $browserAgent->id,
            ]);
        }

        $jwt = TokenFactory::create($user, $session);

        return [
            'user' => $user,
            'token' => $jwt,
            'session' => $session,
            'auth' => UserAction::AUTHENTICATE->value,
        ];
    }
}
