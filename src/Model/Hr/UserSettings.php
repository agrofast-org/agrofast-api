<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class UserSettings extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @unique */
  public User $userId;
  public string $theme;
  public string $language;

  public function compose()
  {
  }
}
