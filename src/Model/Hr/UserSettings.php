<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class UserSettings extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @unique */
  public User|int $userId;
  public string $theme;
  public string $language;

  public function compose()
  {
  }
}
