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
  public Serial $id;
  /** @not_nuable */
  public User $userId;

  public function __construct(
    public Request $requestId,
    public Carrier $carrierId,
    public float $price,
  ) {
  }
}
