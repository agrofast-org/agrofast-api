<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'chat.chat';

    protected $primaryKey = 'uuid';

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
     * @return Collection
     */
    public function getmessage(string $chatUuid)
    {
        return $this->message()
            ->where('uuid', $chatUuid)
            ->orderBy('created_at', 'ASC')
            ->take(50)
            ->get()
        ;
    }

    /**
     * Define a relação com o modelo Message.
     *
     * @return HasMany
     */
    public function message()
    {
        return $this->hasMany(Message::class, 'chat_uuid', 'uuid');
    }
}
