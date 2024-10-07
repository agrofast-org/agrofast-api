<?php

namespace Ilias\Choir\Model\System;

use Ilias\Database\Schemas\System;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Abstract\TrackableTable;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class ErrorLog extends Table
{
  public System $schema;
  /** @primary */
  public Serial $id;
  public string $json;
  public Timestamp | Expression | string $createdIn = Expression::CURRENT_TIMESTAMP;

  public function __construct($json)
  {
    $this->json = $json;
  }
}
