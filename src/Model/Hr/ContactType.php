<?php

namespace Ilias\Choir\Model\Hr;

use Ilias\Choir\Database\Schemas\Hr;
use Ilias\Maestro\Abstract\Table;
use Ilias\Maestro\Types\Serial;

final class ContactType extends Table
{
  public Hr $schema;
  /** @primary */
  public Serial $id;
  /** @not_nuable 
   * @unique */
  public string $label;

  public function __construct()
  {
  }
}
