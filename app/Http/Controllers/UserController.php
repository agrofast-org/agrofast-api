<?php

namespace App\Http\Controllers;

use App\Models\AuthCode;
use App\Models\FilesImage;
use App\Models\Session;
use App\Models\User;
use App\Services\SmsSender;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function list(Request $request)
    {
        return response()->json(['message' => 'not_implemented'], 501);
    }

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

        return response()->json(['message' => 'user_not_found'], 404);
    }

    public function create(Request $request)
    {
        $params = User::prepareInsert($request->all());
        $validated = User::validateInsert(User::prepareInsert($params));

        if (! empty($validated)) {
            return response()->json(['message' => 'error_creating_user', 'fields' => $validated], 400);
        }

        $existingUser = User::where('number', $params['number'])->first();

        if ($existingUser) {
            return response()->json([
                'message' => 'user_already_exists',
                'fields' => [
                    'number' => 'user_already_exists',
                ],
            ], 400);
        }

        $user = User::create($params);

        $authCode = AuthCode::createCode($user->id);
        $data = [
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'auth_code_id' => $authCode->id,
            'last_activity' => Carbon::now()->timestamp,
        ];

        $session = Session::create($data);

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

        $response = [
            'message' => 'user_created_successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'number' => $user->number,
            ],
            'token' => $jwt,
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $params = User::prepareInsert($request->only('name', 'surname', 'email'));
        $validated = User::validateUpdate(User::prepareUpdate($params));

        if (! empty($validated)) {
            return response()->json(['message' => 'error_updating_user', 'fields' => $validated], 400);
        }

        $user->update($params);

        return response()->json(['message' => 'user_updated_successfully'], 200);
    }

    public function login(Request $request)
    {
        $params = $request->only(['number', 'password']);
        if (! isset($params['number']) || ! isset($params['password'])) {
            return response()->json(['message' => 'invalid_login_credentials'], 400);
        }

        $params = User::prepareInsert($request->all());
        $user = User::where('number', $params['number'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'user_not_found',
                'fields' => [
                    'number' => 'user_not_found',
                ],
            ], 404);
        }

        if (! Hash::check($params['password'], $user->password)) {
            return response()->json(['message' => 'wrong_password'], 401);
        }

        $authCode = AuthCode::createCode($user->id);
        $session = Session::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'auth_code_id' => $authCode->id,
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
            ],
            env('APP_KEY'),
            'HS256'
        );

        $response = [
            'message' => 'login_successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
                'surname' => $user->surname,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
            ],
            'token' => $jwt,
        ];

        return response()->json($response, 200);
    }

    public function authenticate(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));

        $session = Session::where('id', $decoded->sid)->first();

        $authCode = AuthCode::where('id', $session->auth_code_id)->first();

        if (! $authCode) {
            return response()->json([
                'message' => 'invalid_authentication_code',
                'fields' => [
                    'code' => 'invalid_authentication_code',
                ],
            ], 400);
        }

        $authCode->update(['attempts' => $authCode->attempts + 1]);
        if ($authCode->code !== $request->input('code')) {
            if ($authCode->attempts >= AuthCode::MAX_ATTEMPTS) {
                $authCode->update(['active' => false]);
                $session->update(['active' => false]);

                return response()->json([
                    'message' => 'authentication_code_attempts_exceeded',
                    'fields' => [
                        'code' => 'authentication_code_attempts_exceeded',
                    ],
                ], 401);
            }

            return response()->json([
                'message' => 'incorrect_authentication_code',
                'fields' => [
                    'code' => 'incorrect_authentication_code',
                ],
                'attempts_left' => AuthCode::MAX_ATTEMPTS - $authCode->attempts,
            ], 400);
        }

        $authCode->update(['active' => false]);
        $session->update(['authenticated' => true]);

        $updateUser = User::where('id', $user->id);
        if (! $updateUser->number_verified) {
            $updateUser->update([
                'number_authenticated' => true,
                'number_verified_at' => now(),
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

        $response = [
            'message' => 'user_authenticated_successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'number' => $user->number,
                'surname' => $user->surname,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
            ],
            'token' => $jwt,
        ];

        return response()->json($response, 200);
    }

    public function resendCode(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));

        $session = Session::where('id', $decoded->sid);

        $recentCode = AuthCode::where('user_id', $user->id)
            ->where('session_id', $session->id)
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($recentCode) {
            SmsSender::send($user->number, "Seu código de autenticação para o Agrofast é: {$recentCode->code}");

            return response()->json(['message' => 'authentication_code_resent'], 200);
        }

        try {
            AuthCode::createCode($user->id);

            return response()->json(['message' => 'authentication_code_resent'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'failed_to_resend_code', 'error' => $e->getMessage()], 500);
        }
    }

    public function self(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $response = [
            'message' => 'user_found',
        ];

        $token = $request->bearerToken();
        if ($token) {
            $decoded = JWT::decode($token, new Key(env('APP_KEY'), 'HS256'));
            $response['user'] = $user;
            if (! isset($decoded->sid)) {
                $response['user'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'number' => $user->number,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'profile_picture' => $user->profile_picture,
                ];
            }
        }

        return response()->json($response, 200);
    }

    public function info($id)
    {
        $user = User::find($id, ['id', 'name', 'number', 'profile_picture']);

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        return response()->json(['data' => $user], 200);
    }

    public function picture($userId, $pictureUuid = null)
    {
        $files = Storage::files("uploads/pictures/{$userId}");
        if (empty($files)) {
            return response()->json(['message' => 'no_images_found'], 404);
        }

        if ($pictureUuid) {
            $file = Storage::get("uploads/pictures/{$userId}/{$pictureUuid}");
            $type = Storage::mimeType("uploads/pictures/{$userId}/{$pictureUuid}");

            return response($file, 200)->header('Content-Type', $type);
        }

        $lastFile = end($files);
        $file = Storage::get($lastFile);
        $type = Storage::mimeType($lastFile);

        return response($file, 200)->header('Content-Type', $type);
    }

    public function postPicture(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $validated['image'];

        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs(
            "uploads/pictures/{$user->id}",
            $fileName,
            env('FILESYSTEM_DISK', 's3')
        );

        if (! $path) {
            return response()->json(['message' => 'failed_to_upload_image'], 500);
        }

        $fileRecord = FilesImage::create([
            'id' => Str::uuid(),
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);

        $appUrl = env('APP_URL');
        $user = User::where('id', $user->id)->first();
        $user->update([
            'profile_picture' => "{$appUrl}/{$path}",
        ]);

        if (! $fileRecord) {
            Storage::disk(env('FILESYSTEM_DISK', 's3'))->delete($path);

            return response()->json(['message' => 'failed_to_save_image_record'], 500);
        }

        return response()->json([
            'message' => 'image_uploaded_successfully',
            'profile_picture' => "{$appUrl}/{$path}",
        ], 201);
    }

    public function exists(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
        ]);

        $user = User::where('number', $validated['number'])->first();

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        return response()->json(['message' => 'user_found', 'data' => $user], 200);
    }
}
