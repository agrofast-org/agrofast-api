<?php

namespace App\Http\Controllers;

use App\Models\AuthCode;
use App\Models\Session;
use App\Models\User;
use App\Services\SmsSender;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $query = $request->only(['id', 'telephone', 'name']);
        $userQuery = User::query();

        if (! empty($query['id'])) {
            $userQuery->where('id', $query['id']);
        } elseif (! empty($query['telephone'])) {
            $userQuery->where('number', $query['telephone']);
        } elseif (! empty($query['name'])) {
            $userQuery->where('name', 'like', '%'.$query['name'].'%');
        }

        $user = $userQuery->first();

        if ($user) {
            return response()->json(['data' => $user], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function list(Request $request)
    {
        // TODO: Implement list method with complex pagination and filtering

        return response()->json(['message' => 'Not implemented'], 501);
    }

    public function self()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        return response()->json(['data' => $user], 200);
    }

    public function info($id)
    {
        $user = User::find($id, ['id', 'name', 'number', 'profile_picture']);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['data' => $user], 200);
    }

    public function exists(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
        ]);

        $user = User::where('number', $validated['number'])->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User found', 'data' => $user], 200);
    }

    public function create(Request $request)
    {
        $params = User::prepareInsert($request->all());
        $validated = User::validateInsert(User::prepareInsert($params));

        if (! empty($validated)) {
            return response()->json(['message' => 'Error creating user', 'fields' => $validated], 400);
        }

        $existingUser = User::where('number', $params['number'])->first();

        if ($existingUser) {
            return response()->json(['message' => 'User already exists'], 400);
        }

        $user = User::create($params);

        AuthCode::createCode($user->id);

        $jwt = JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'aud' => 'agrofast-app-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
            ],
            env('APP_KEY'),
            'HS256'
        );

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
            ],
            'token' => $jwt,
        ];

        return response()->json(['message' => 'User created successfully', 'data' => $data], 201);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validated = User::validateInsert($request->all());

        if (! empty($validated)) {
            return response()->json(['message' => 'Error creating user', 'fields' => $validated], 400);
        }

        $user->update($validated);

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function authenticate(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $authCode = AuthCode::where('user_id', $user->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $authCode) {
            return response()->json(['message' => 'Invalid authentication code'], 400);
        }

        if ($authCode->code !== $validated['code']) {
            $authCode->update(['attempts' => $authCode->attempts + 1]);

            return response()->json(['message' => 'Invalid authentication code'], 400);
        }

        $authCode->update(['active' => false]);

        $user->update(['number_authenticated' => true]);

        $session = Session::create([
            'user_id' => $user->id,
            'ip_address' => "'{$request->ip()}'",
            'user_agent' => "'{$request->userAgent()}'",
            'payload' => json_encode($request->all()),
            'last_activity' => Carbon::now()->timestamp,
        ]);

        $jwt = JWT::encode(
            [
                'iss' => env('APP_URL'),
                'sub' => $user->id,
                'sid' => $session->id,
                'aud' => 'agrofast-app-services',
                'iat' => now()->timestamp,
                'jti' => uniqid(),
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
            ],
            env('APP_KEY'),
            'HS256'
        );

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
                'picture' => $user->profile_picture,
            ],
            'token' => $jwt,
        ];

        return response()->json(['message' => 'User authenticated successfully', 'data' => $data], 200);
    }

    public function resendCode()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $recentCode = AuthCode::where('user_id', $user->id)
            ->where('active', true)
            ->where('created_at', '>=', Carbon::now()->subMinutes(3))
            ->first();

        if ($recentCode) {
            SmsSender::send($user->number, "Seu código de autenticação para o Agrofast é: {$recentCode->code}");

            return response()->json(['message' => 'Authentication code resent'], 200);
        }

        try {
            AuthCode::createCode($user->id);

            return response()->json(['message' => 'Authentication code resent'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to resend code', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('number', $validated['number'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $jwt = JWT::encode(
            ['id' => $user->id, 'name' => $user->name, 'number' => $user->number],
            env('APP_KEY'),
            'HS256'
        );

        try {
            AuthCode::createCode($user->id);

            return response()->json(['message' => 'Login successful, authentication code sent', 'token' => $jwt], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send authentication code', 'error' => $e->getMessage()], 500);
        }
    }
}
