<?php

namespace Ilias\Choir\Model\Chat;

use Ilias\Choir\Database\Schemas\Chat;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Message extends TrackableTable
{
  public Chat $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $fromUserId;
  /** @not_nuable */
  public User|int $toUserId;
  /** @nuable */
  public Message|int|null $answerTo = null;
  /** @not_nuable */
  public string $message;

  public function compose(string $message): void
  {
    $this->message = $message;
  }
}
