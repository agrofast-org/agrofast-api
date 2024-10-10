<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Session extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable */
  public User $userId;
  /** @not_nuable */
  public string $token;

  public function compose()
  {
  }
}
