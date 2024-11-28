<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
  use HasFactory;

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
   * Get the messages for the chat.
   *
   * @param string $chatUuid
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function getMessages(string $chatUuid)
  {
    return $this->messages()
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
  public function messages()
  {
    return $this->hasMany(Message::class, 'chat_uuid', 'uuid');
  }
}
