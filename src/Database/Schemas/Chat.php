<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\Chat\ChatUser;
use Ilias\Choir\Model\Chat\Message;
use Ilias\Choir\Model\Chat\Reactions;
use Ilias\Maestro\Abstract\Schema;

final class Chat extends Schema
{
  public Chat $chat;
  public ChatUser $chatUser;
  public Message $message;
  public Reactions $reactions;
}
