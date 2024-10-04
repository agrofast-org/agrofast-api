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
  public Serial $id;
  /** @not_nuable */
  public User $fromUserId;
  /** @not_nuable */
  public User $toUserId;
  /** @nuable */
  public ?Message $answerTo = null;
  /** @not_nuable */
  public string $message;
}
