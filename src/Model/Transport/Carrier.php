<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Choir\Model\Vehicle;
use Ilias\Maestro\Types\Serial;

final class Carrier extends Vehicle
{
  public Transport $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
}
