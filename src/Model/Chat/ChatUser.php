<?php

namespace Ilias\Choir\Model\Chat;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class ChatUser extends Table
{
  public Chat $schema;
  /** @primary */
  public Serial|int $id;
  public Chat|int $chatId;
  public User|int $userId;
  public bool $active = true;
  public Timestamp|Expression|string|null $joinedIn = Expression::CURRENT_TIMESTAMP;
  public Timestamp|string|null $leftIn = null;

  public function compose(int $chatId, int $userId)
  {
    $this->chatId = $chatId;
    $this->userId = $userId;
  }
}
