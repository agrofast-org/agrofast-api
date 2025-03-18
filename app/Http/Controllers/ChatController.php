<?php

namespace App\Http\Controllers;

use App\Models\Chat\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Retrieve chat for the authenticated user.
     */
    public function getUserchat(): JsonResponse
    {
        // Retrieve the authenticated user
        $user = Auth::user();

        // Fetch chat for the authenticated user
        $chat = Message::getUserchat($user->id);

        return response()->json([
            'data' => $chat,
        ], 200);
    }
}
