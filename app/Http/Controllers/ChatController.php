<?php

namespace App\Http\Controllers;

use App\Models\Chat\Chat;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function index()
    {
        $user = User::auth();
        $chats = $user->chats()->get();

        return response()->json($chats);
    }

    public function show(string $uuid)
    {
        $user = User::auth();
        $chat = Chat::where('uuid', $uuid)->first();

        if (!$this->chatService->userBelongsToChat($user->id, $chat->id)) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $chat = Chat::where('uuid', $uuid)
            ->with([
                'users',
                'messages' => function ($query) {
                    $query->orderBy('created_at', 'desc')->limit(40);
                },
                'messages.answer_to',
            ])
            ->first()
        ;

        return response()->json($chat);
    }
}
