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
  public Serial $id;
  /** @not_nuable */
  public User $userId;

  public function __construct(
    public string $origin,
    public string $destination,
    public Timestamp $desiredDate,
  ) {
  }
}
