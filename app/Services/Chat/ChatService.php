<?php

namespace App\Services\Chat;

use App\Exception\InvalidFormException;
use App\Models\Chat\Chat;
use App\Models\Chat\ChatUser;
use App\Models\Chat\Message;
use Illuminate\Support\Str;

class ChatService
{
    public function createPrivateChat(int $userA, int $userB, ?string $firstMessage = null): Chat
    {
        $existingChat = Chat::whereHas('users', function ($q) use ($userA, $userB) {
            $q->whereIn('user_id', [$userA, $userB]);
        })
            ->whereHas('users', fn ($q) => $q->groupBy(['chat_id', 'chat_user.id'])->havingRaw('count(distinct user_id) = 2'))
            ->first()
        ;

        if ($existingChat) {
            return $existingChat;
        }

        $chat = Chat::create([
            'uuid' => (string) Str::uuid(),
            'active' => true,
        ]);

        foreach ([$userA, $userB] as $userId) {
            ChatUser::create([
                'chat_id' => $chat->id,
                'user_id' => $userId,
            ]);
        }

        if ($firstMessage) {
            Message::create([
                'uuid' => (string) Str::uuid(),
                'chat_id' => $chat->id,
                'user_id' => $userA,
                'message' => $firstMessage,
            ]);
        }

        return $chat;
    }

    public function sendMessage(int $chatId, int $userId, string $content, ?int $answerTo = null): Message
    {
        $chat = Chat::where('id', $chatId)->where('active', true)->first();
        if (!$chat) {
            throw new InvalidFormException('Chat não encontrado ou inativo.', [
                'chat_uuid' => ['O chat especificado não existe ou está inativo.'],
            ]);
        }

        if (!$this->userBelongsToChat($userId, $chatId)) {
            throw new InvalidFormException('Usuário não pertence a este chat.', [
                'chat_uuid' => ['O usuário não pertence a este chat.'],
            ]);
        }

        $message = Message::create([
            'uuid' => (string) Str::uuid(),
            'chat_id' => $chat->id,
            'user_id' => $userId,
            'message' => $content,
            'answer_to' => $answerTo,
            'active' => true,
        ]);

        $chat->touch();

        return $message;
    }

    public function userBelongsToChat(string $userId, string $chatId): bool
    {
        $chat = Chat::find($chatId);
        if (!$chat) {
            return false;
        }

        return $chat->users()->where('user_id', $userId)->exists();
    }
}
