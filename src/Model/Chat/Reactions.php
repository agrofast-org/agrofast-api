<?php

namespace Ilias\Choir\Model\Chat;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Reactions extends Table
{
  public Chat $schema;
  /** @primary */
  public Serial|int $id;
  public Message|int $messageId;
  public User|int $userId;
  public string $element;

  public function compose(int $messageId, int $userId, string $element)
  {
    $this->messageId = $messageId;
    $this->userId = $userId;
    $this->element = $element;
  }
}
