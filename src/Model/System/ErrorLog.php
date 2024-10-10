<?php

namespace Ilias\Choir\Model\System;

use Ilias\Choir\Database\Schemas\System;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Database\Expression;
use Ilias\Maestro\Types\Serial;
use Ilias\Maestro\Types\Timestamp;

final class ErrorLog extends Table
{
  public System $schema;
  /** @primary */
  public Serial $id;
  public string $json;
  public string $params;
  public Timestamp | Expression | string $createdIn = Expression::CURRENT_TIMESTAMP;

  public function compose($json, $params, $createdIn = null)
  {
    $this->json = $json;
    $this->params = $params;
    if (empty($createdIn)) {
      $createdIn = new Timestamp('now');
    }
    $this->createdIn = $createdIn;
  }
}
