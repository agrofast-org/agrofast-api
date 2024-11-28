<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
  /**
   * Retrieve chats for the authenticated user.
   *
   * @return JsonResponse
   */
  public function getUserChats(): JsonResponse
  {
    // Retrieve the authenticated user
    $user = Auth::user();

    // Fetch chats for the authenticated user
    $chats = Message::getUserChats($user->id);

    return response()->json([
      'data' => $chats,
    ], 200);
  }
}
