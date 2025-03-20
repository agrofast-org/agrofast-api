<?php

namespace App\Models\Chat;

use App\Models\Hr\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    use HasFactory;

    protected $table = 'chat.message';

    protected $fillable = [
        'user_id',
        'chat_id',
        'answer_to',
        'message',
    ];

    /**
     * Relationship with Chat.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'uuid');
    }

    /**
     * Relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Send a new message.
     *
     * @throws \Exception
     */
    public static function sendMessage(int $userId, string $chatId, string $message, ?int $answerTo = null): self
    {
        DB::beginTransaction();

        try {
            $messageData = [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'message' => $message,
            ];

            if (!empty($answerTo)) {
                $messageData['answer_to'] = $answerTo;
            }

            $newMessage = self::create($messageData);

            DB::commit();

            return $newMessage;
        } catch (\Throwable $e) {
            DB::rollBack();

            throw new \Exception('Failed to send message: '.$e->getMessage());
        }
    }

    /**
     * Retrieve chat for a user.
     *
     * @return null|Collection
     */
    public static function getUserchat(int $userId)
    {
        return DB::table('message as m')
            ->select('m.message', 'm.user_id', 'u.name', DB::raw('MAX(m.created_at) as last_message_time'))
            ->join('user as u', 'u.id', '=', 'm.user_id')
            ->where('m.chat_id', $userId)
            ->groupBy('m.user_id', 'm.message', 'u.name')
            ->get()
        ;
    }
}
