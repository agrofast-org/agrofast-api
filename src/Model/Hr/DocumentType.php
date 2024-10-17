<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class DocumentType extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial|int $id;
  /** @unique */
  public string $name;
  /** @unique */
  public string $label;
  public bool $active = true;

  public function compose(string $name, string $label)
  {
    $this->name = $name;
    $this->label = $label;
  }
}
