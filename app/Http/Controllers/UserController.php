<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserProfileTypeRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Hr\AuthCode;
use App\Models\Hr\User;
use App\Services\AuthService;
use App\Services\PictureService;
use App\Services\UserDocumentService;
use App\Services\UserQueryService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    protected $authService;

    protected $userQueryService;

    protected $pictureService;
    protected $userDocumentService;

    public function __construct(
        UserService $userService,
        AuthService $authService,
        UserQueryService $userQueryService,
        PictureService $pictureService,
        UserDocumentService $userDocumentService
    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->userQueryService = $userQueryService;
        $this->pictureService = $pictureService;
        $this->userDocumentService = $userDocumentService;
    }

    public function index(Request $request)
    {
        $query = $request->only(['id', 'telephone', 'name']);
        $user = $this->userQueryService->getUser($query);

        if ($user) {
            return response()->json($user, 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->userService->createUser($data, $request);

        return response()->json($result, 201);
    }

    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update($data);

        $documents = $request->input('documents', []);
        if (!empty($documents)) {
            $this->userDocumentService->handleList(null, $documents);
        }

        return response()->json(UserDataResponse::withDocument($user), 200);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only(['email', 'password', 'remember']);

        $result = $this->authService->login($credentials, $request);

        return response()->json($result, 200);
    }

    public function resendCode()
    {
        $result = $this->authService->resendCode();

        return response()->json($result, 200);
    }

    public function authenticate(Request $request)
    {
        $result = $this->authService->authenticate($request);

        return response()->json($result, 200);
    }

    public function setProfileType(UserProfileTypeRequest $request)
    {
        $user = User::auth();

        $user->update(['profile_type' => $request->input('profile_type')]);

        return response()->json(UserDataResponse::withDocument($user), 200);
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

        return response()->json($methods, 200);
    }

    public function self()
    {
        $session = User::session();
        if (!$session) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $session = User::session();

        return response()->json([
            ...UserDataResponse::withDocument($user),
            'authenticated' => $session->authenticated,
        ]);
    }

    public function info($uuid)
    {
        $user = $this->userQueryService->getInfo($uuid);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    public function picture($userUuid, $pictureUuid = null)
    {
        $result = $this->pictureService->getPicture($userUuid, $pictureUuid);

        return response($result->data['file'], 200)->header('Content-Type', $result->data['mime']);
    }

    public function postPicture(Request $request)
    {
        $user = User::auth();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $result = $this->pictureService->uploadPicture($request, $user);

        return response()->json($result, 201);
    }

    public function exists(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
        ]);

        $user = $this->userQueryService->exists($validated['number']);

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        return response()->json($user, 200);
    }

    public function codeLength()
    {
        return response()->json(['length' => AuthCode::LENGTH], 200);
    }

    public function profileType()
    {
        $user = User::auth();
        $user->update(['profile_type' => request('profile_type')]);

        return response()->json(UserDataResponse::withDocument($user), 200);
    }
}
