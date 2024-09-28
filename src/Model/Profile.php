<?php

namespace Ilias\Choir\Model;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class Profile extends Table
{
  public Hr $schema;
  /** @primary */
  /** @not_nuable */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
  public ProfileType $profileType;
}
