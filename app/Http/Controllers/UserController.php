<?php

namespace App\Http\Controllers;

use App\Factories\ResponseFactory;
use App\Http\Requests\User\UserProfileTypeRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Responses\User\UserDataResponse;
use App\Models\Error;
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
            return ResponseFactory::success('user_found', $user);
        }

        return ResponseFactory::error('user_not_found', null, null, 404);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        $result = $this->userService->createUser($data, $request);

        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return ResponseFactory::success('user_created_successfully', $result, 201);
    }

    public function update(UserUpdateRequest $request)
    {
        $data = $request->only(['name', 'surname']);
        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, null, 404);
        }

        $user->update($data);

        $documents = $request->input('documents', []);
        if (!empty($documents)) {
            $this->userDocumentService->handleList(null, $documents);
        }

        return ResponseFactory::success(
            'user_updated',
            UserDataResponse::withDocument($user),
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password', 'remember']);

        $result = $this->authService->login($credentials, $request);

        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return ResponseFactory::success('login_authentication_code_sent', $result);
    }

    public function resendCode()
    {
        $result = $this->authService->resendCode();
        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return ResponseFactory::success('code_resend_successfully', $result);
    }

    public function authenticate(Request $request)
    {
        $result = $this->authService->authenticate($request);

        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return ResponseFactory::success('user_authenticated_successfully', $result);
    }

    public function setProfileType(UserProfileTypeRequest $request)
    {
        $user = User::auth();

        $user->update(['profile_type' => $request->input('profile_type')]);

        return ResponseFactory::success(
            'profile_type_set_successfully',
            UserDataResponse::withDocument($user)
        );
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

        return ResponseFactory::success('available_authentication_methods', ['methods' => $methods]);
    }

    public function self()
    {
        $session = User::session();
        if (!$session) {
            return ResponseFactory::error('user_not_authenticated', null, null, 401);
        }

        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_authenticated', null, null, 401);
        }

        $session = User::session();

        return ResponseFactory::success('user_found', [
            ...UserDataResponse::withDocument($user),
            'authenticated' => $session->authenticated,
        ]);
    }

    public function info($uuid)
    {
        $user = $this->userQueryService->getInfo($uuid);

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, null, 404);
        }

        return ResponseFactory::success('user_found', $user);
    }

    public function picture($userUuid, $pictureUuid = null)
    {
        $result = $this->pictureService->getPicture($userUuid, $pictureUuid);

        if ($result instanceof Error) {
            return ResponseFactory::create($result);
        }

        return response($result->data['file'], 200)->header('Content-Type', $result->data['mime']);
    }

    public function postPicture(Request $request)
    {
        $user = User::auth();

        if (!$user) {
            return ResponseFactory::error('user_not_authenticated', null, null, 401);
        }

        $result = $this->pictureService->uploadPicture($request, $user);

        if ($result instanceof Error) {
            return ResponseFactory::error('error_uploading_image', null, $result);
        }

        return ResponseFactory::success('image_uploaded_successfully', $result, 201);
    }

    public function exists(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:255',
        ]);

        $user = $this->userQueryService->exists($validated['number']);

        if (!$user) {
            return ResponseFactory::error('user_not_found', null, null, 404);
        }

        return ResponseFactory::success('user_found', $user);
    }

    public function codeLength()
    {
        return ResponseFactory::success('code_length', ['length' => AuthCode::LENGTH]);
    }
}
