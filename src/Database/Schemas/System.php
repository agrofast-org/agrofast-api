<?php

namespace Ilias\Choir\Database\Schemas;

use Ilias\Choir\Model\System\ErrorLog;
use Ilias\Maestro\Abstract\Schema;

final class System extends Schema
{
  public ErrorLog $errorLog;
}
