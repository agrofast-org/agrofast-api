<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Session extends TrackableTable
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public AuthCode|int $authCodeId;
  /** @not_nuable */
  public bool $authenticated = false;
  /** @not_nuable */
  public string $tempToken;

  public function compose()
  {
  }
}
