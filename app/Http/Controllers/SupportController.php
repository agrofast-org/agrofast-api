<?php

namespace App\Http\Controllers;

use App\Http\Requests\Support\SupportStoreRequest;
use App\Models\Hr\User;
use App\Services\Chat\ChatService;

class SupportController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {
    }

    public function store(SupportStoreRequest $request)
    {
        $user = User::auth();
        $validated = $request->validated();

        $chat = $this->chatService->createChatWithSupport($user->id);

        $messages = [];

        if (!empty($validated['message'])) {
            $messages[] = $this->chatService->sendMessage($chat->id, $user->id, $validated['message'], $validated['answer_to'] ?? null);
            return response()->json([
                'chat_uuid' => $chat->uuid,
            ], 201);
        }
        foreach ($validated['messages'] as $messageData) {
            $messages[] = $this->chatService->sendMessage(
                $chat->id,
                $user->id,
                $messageData['message'],
                $messageData['answer_to'] ?? null
            );
        }

        return response()->json([
            'chat_uuid' => $chat->uuid,
        ], 201);
    }

    public function delete(string $uuid)
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
