<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class ProfileType extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @unique */
  public string $label;

  public function compose(string $label) {
    $this->label = $label;
  }
}