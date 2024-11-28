<?php

namespace App\Http\Controllers;

use App\Models\AuthCode;
use App\Models\User;
use App\Services\SmsSender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
  /**
   * Get a single user by ID, phone, or name.
   */
  public function getUser(Request $request)
  {
    $query = $request->only(['id', 'telephone', 'name']);
    $userQuery = User::query();

    if (!empty($query['id'])) {
      $userQuery->where('id', $query['id']);
    } elseif (!empty($query['telephone'])) {
      $userQuery->where('number', $query['telephone']);
    } elseif (!empty($query['name'])) {
      $userQuery->where('name', 'like', '%' . $query['name'] . '%');
    }

    $user = $userQuery->first();

    if ($user) {
      return response()->json(['data' => $user], 200);
    }

    return response()->json(['message' => 'User not found'], 404);
  }

  /**
   * Get authenticated user info.
   */
  public function getUserInfo()
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    return response()->json(['data' => $user], 200);
  }

  /**
   * Check if a user exists by phone number.
   */
  public function checkIfExists(Request $request)
  {
    $validated = $request->validate([
      'number' => 'required|string|max:255',
    ]);

    $user = User::where('number', $validated['number'])->first();

    if (!$user) {
      return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json(['message' => 'User found', 'data' => $user], 200);
  }

  /**
   * Create a new user.
   */
  public function createUser(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'surname' => 'required|string|max:255',
      'number' => 'required|string|max:255|unique:users,number',
      'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create($validated);

    $jwt = JWT::encode(
      ['id' => $user->id, 'name' => $user->name, 'number' => $user->number],
      env('APP_JWT_SECRET'),
      'HS256'
    );

    return response()->json(['message' => 'User created successfully', 'token' => $jwt], 201);
  }

  /**
   * Update an existing user.
   */
  public function updateUser(Request $request)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not found'], 404);
    }

    $validated = $request->validate([
      'name' => 'sometimes|string|max:255',
      'surname' => 'sometimes|string|max:255',
      'number' => 'sometimes|string|max:255|unique:users,number,' . $user->id,
    ]);

    $user->update($validated);

    return response()->json(['message' => 'User updated successfully'], 200);
  }

  public function authenticateUser(Request $request)
  {
    $user = Auth::user();

    if (!$user) {
      return response()->json(['message' => 'User not authenticated'], 401);
    }

    $validated = $request->validate([
      'code' => 'required|string',
    ]);

    $authCode = AuthCode::where('user_id', $user->id)
      ->where('active', true)
      ->orderBy('created_at', 'desc')
      ->first();

    if (!$authCode || $authCode->code !== $validated['code']) {
      return response()->json(['message' => 'Invalid authentication code'], 400);
    }

    $authCode->update(['active' => false]);

    $user->update(['authenticated' => true]);

    return response()->json(['message' => 'User authenticated successfully'], 200);
  }

  /**
   * Resend authentication code to the user.
   */
  public function resendCode()
  {
    $user = Auth::user();

    if (!$user) {
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

  /**
   * User login method that initiates the authentication process.
   */
  public function userLogin(Request $request)
  {
    $validated = $request->validate([
      'number' => 'required|string',
      'password' => 'required|string',
    ]);

    $user = User::where('number', $validated['number'])->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
      return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $jwt = JWT::encode(
      ['id' => $user->id, 'name' => $user->name, 'number' => $user->number],
      env('APP_JWT_SECRET'),
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
