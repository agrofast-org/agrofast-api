<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chat.chat';

    protected $primaryKey = 'uuid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'name',
        'picture',
        'is_group',
    ];

    /**
     * Get the message for the chat.
     *
     * @param string $chatUuid
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getmessage(string $chatUuid)
    {
        return $this->message()
            ->where('uuid', $chatUuid)
            ->orderBy('created_at', 'ASC')
            ->take(50)
            ->get();
    }

    /**
     * Define a relação com o modelo Message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function message()
    {
        return $this->hasMany(Message::class, 'chat_uuid', 'uuid');
    }
}
