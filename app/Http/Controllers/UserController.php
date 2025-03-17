<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Hr\AuthCode;
use App\Models\Hr\User;
use App\Services\AuthService;
use App\Services\PictureService;
use App\Services\UserQueryService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    protected $authService;

    protected $userQueryService;

    protected $pictureService;

    public function __construct(
        UserService $userService,
        AuthService $authService,
        UserQueryService $userQueryService,
        PictureService $pictureService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->userQueryService = $userQueryService;
        $this->pictureService = $pictureService;
    }

    public function index(Request $request)
    {
        $query = $request->only(['id', 'telephone', 'name']);
        $user = $this->userQueryService->getUser($query);

        if ($user) {
            return response()->json(['data' => $user], 200);
        }

        return response()->json(['message' => 'user_not_found'], 404);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->userService->createUser($data, $request);

        if (isset($result['error'])) {
            return response()->json([
                'message' => 'error_creating_user',
                'errors'  => $result['error'],
            ], 400);
        }

        return response()->json([
            'message' => 'user_created_successfully',
            'user'    => [
                'id'      => $result['user']->id,
                'uuid'      => $result['user']->uuid,
                'name'    => $result['user']->name,
                'surname' => $result['user']->surname,
                'email'   => $result['user']->email,
                'number'  => $result['user']->number,
            ],
            'token'   => $result['token'],
            'action'  => $result['auth'],
        ], 201);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $data = $request->only(['id', 'name', 'password', 'email']);
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        $user->update($data);

        return response()->json(['message' => 'user_updated_successfully'], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password', 'remember']);

        if (! isset($credentials['email']) || ! isset($credentials['password'])) {
            return response()->json(['message' => 'invalid_login_credentials'], 400);
        }

        $result = $this->authService->login($credentials, $request);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'login_successful_authentication_code_sent',
            'user'    => [
                'id'              => $result['user']->id,
                'uuid'            => $result['user']->uuid,
                'name'            => $result['user']->name,
                'surname'         => $result['user']->surname,
                'email'           => $result['user']->email,
                'number'          => $result['user']->number,
                'profile_picture' => $result['user']->profile_picture,
            ],
            'token'   => $result['token'],
            'action'  => $result['auth'],
        ], 200);
    }

    public function authenticate(Request $request)
    {
        $result = $this->authService->authenticate($request);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 400);
        }

        return response()->json([
            'message' => 'user_authenticated_successfully',
            'user'    => [
                'id'              => $result['user']->id,
                'uuid'      => $result['user']->uuid,
                'name'            => $result['user']->name,
                'surname'         => $result['user']->surname,
                'email'           => $result['user']->email,
                'number'          => $result['user']->number,
                'profile_picture' => $result['user']->profile_picture,
            ],
            'token'   => $result['token'],
        ], 200);
    }

    public function authenticationMethods()
    {
        $user = User::auth();
        $methods = [];

        if ($user->number_verified === true) {
            $methods[] = 'sms';
        }

        if ($user->email_verified === true) {
            $methods[] = 'email';
        }

        return response()->json([
            'methods' => $methods,
        ], 200);
    }

    public function self()
    {
        $user = User::auth();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $session = User::session();

        return response()->json([
            'message' => 'user_found',
            'user'    => [
                'id'              => $user->id,
                'uuid'            => $user->uuid,
                'name'            => $user->name,
                'surname'         => $user->surname,
                'email'           => $user->email,
                'number'          => $user->number,
                'profile_picture' => $user->profile_picture,
            ],
            'authenticated' => $session->authenticated,
        ], 200);
    }

    public function info($id)
    {
        $user = $this->userQueryService->getInfo($id);

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        return response()->json(['data' => $user], 200);
    }

    public function picture($userId, $pictureUuid = null)
    {
        $result = $this->pictureService->getPicture($userId, $pictureUuid);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 404);
        }

        return response($result['file'], 200)->header('Content-Type', $result['mime']);
    }

    public function postPicture(Request $request)
    {
        $user = User::auth();

        if (! $user) {
            return response()->json(['message' => 'user_not_authenticated'], 401);
        }

        $result = $this->pictureService->uploadPicture($request, $user);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], 500);
        }

        return response()->json([
            'message' => 'image_uploaded_successfully',
            'file'    => $result['file'],
        ], 201);
    }

    public function exists(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
        ]);

        $user = $this->userQueryService->exists($validated['number']);

        if (! $user) {
            return response()->json(['message' => 'user_not_found'], 404);
        }

        return response()->json([
            'message' => 'user_found',
            'data'    => $user,
        ], 200);
    }

    public function codeLength()
    {
        return response()->json([
            'message' => 'code_length',
            'length'  => AuthCode::LENGTH,
        ], 200);
    }
}
