<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Hr\User;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Types\Serial;

final class Offer extends TrackableTable
{
  public Transport $schema;
  /** @primary */
  public Serial|int $id;
  /** @not_nuable */
  public User|int $userId;
  /** @not_nuable */
  public Request|int $requestId;
  /** @not_nuable */
  public Carrier|int $carrierId;
  /** @not_nuable */
  public float $price;

  public function compose()
  {
  }
}
