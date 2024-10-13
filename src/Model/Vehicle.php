<?php

namespace Ilias\Choir\Model;

use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
class Vehicle extends TrackableTable
{
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public string $name;
  /** @not_nuable */
  public string $model;
  /** @not_nuable
   * @unique */
  public string $plate;
}
