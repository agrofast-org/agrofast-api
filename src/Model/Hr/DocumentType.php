<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class DocumentType extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @unique */
  public string $label;

  public function __construct(string $label) {
    $this->label = $label;
  }
}
