<?php

namespace App\Http\Controllers;

use App\Models\Chat\Chat;
use App\Models\Chat\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Retrieve message for a specific chat.
     */
    public function getmessage(string $chatUuid): JsonResponse
    {
        $chat = Chat::where('uuid', $chatUuid)->first();

        if (!$chat) {
            return response()->json(['message' => 'Chat not found'], 404);
        }

        $message = $chat->message()->orderBy('created_at', 'asc')->get();

        return response()->json([
            'data' => $message,
        ], 200);
    }

    /**
     * Send a new message to a chat.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'chat_id' => 'required|exists:chat,uuid',
            'message' => 'required|string',
            'answer_to' => 'nullable|exists:message,id',
        ]);

        try {
            $message = Message::create([
                'user_id' => $user->id,
                'chat_id' => $validated['chat_id'],
                'message' => $validated['message'],
                'answer_to' => $validated['answer_to'] ?? null,
            ]);

            return response()->json([
                'message' => 'Message sent successfully',
                'data' => $message,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a message.
     */
    public function deleteMessage(int $id): JsonResponse
    {
        $user = Auth::user();
        $message = Message::where('id', $id)->where('user_id', $user->id)->first();

        if (!$message) {
            return response()->json(['message' => 'Message not found or you do not have permission to delete it'], 404);
        }

        try {
            $message->delete();

            return response()->json(['message' => 'Message deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete message', 'error' => $e->getMessage()], 500);
        }
    }
}
