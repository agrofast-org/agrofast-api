<?php

namespace Ilias\Choir\Model\Transport;

use Ilias\Choir\Database\Schemas\Transport;
use Ilias\Choir\Model\Vehicle;
use Ilias\Maestro\Types\Serial;

final class Machinery extends Vehicle
{
  public Transport $schema;
  /** @primary */
  public Serial $id;

  public function __construct(
    public string $name,
    public string $model,
    public string $plate,
  ) {
  }
}
