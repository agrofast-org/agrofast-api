<?php

namespace App\Factories;

use App\Models\Hr\AuthEmail;
use App\Models\Hr\AuthSms;
use App\Models\Hr\BrowserAgent;
use App\Models\Hr\Session;
use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class SessionFactory
{
    public static function create(User $user, FormRequest $request, BrowserAgent $browserAgent, AuthEmail | AuthSms | null $authCode): Session
    {
        return Session::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser_agent_id' => $browserAgent->id,
            $authCode && 'auth_code_id' => $authCode->id,
            $authCode && 'auth_type' => $authCode->auth_type,
            'last_activity' => Carbon::now()->timestamp,
        ]);
    }
}
