<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class Request extends TrackableTable
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public string $origin;
  /** @not_nuable */
  public string $destination;
  /** @not_nuable */
  public Timestamp $desiredDate;

  public function compose()
  {
  }
}
