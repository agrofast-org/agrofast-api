<?php

use Ilias\Choir\Database\Schemas\Chat as ChatSchemas;
use Ilias\Choir\Model\Chat\Message;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Unique;

final class Chat extends TrackableTable
{
  public ChatSchemas $schema;
  /** @primary */
  public Unique|string $uuid;
  public string $name = null;
  public string $picture = null;
  public bool $isGroup = false;

  public function compose()
  {
  }

  public function getMessages(string $chatUuid): array
  {
    return Message::fetchAll(['uuid' => $chatUuid], ['created_in' => 'ASC'], 50);  
  }
}
