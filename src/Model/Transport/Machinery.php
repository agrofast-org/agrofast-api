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
  /** @not_nuable */
  public string $name;
  /** @not_nuable */
  public string $model;
  /** @not_nuable */
  public string $plate;

  public function compose() {
  }
}
